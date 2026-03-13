<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Jobs;

use Appsolutely\AIO\Models\NotificationSender;
use Appsolutely\AIO\Repositories\NotificationSenderRepository;
use Appsolutely\AIO\Services\NotificationSenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Job to send a single notification email asynchronously
 *
 * This job should be dispatched for individual email sends to avoid
 * blocking the main request thread and improve user experience.
 */
final class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly ?int $notificationQueueId = null,
        private readonly string $email = '',
        private readonly string $subject = '',
        private readonly string $bodyHtml = '',
        private readonly ?string $bodyText = null,
        private readonly ?int $senderId = null
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $sender        = $this->resolveSender();
            $senderService = app(NotificationSenderService::class);

            $mailer = $senderService->getMailer($sender);
            $from   = $senderService->getFromAddress($sender);

            $mailer->send([], [], function ($message) use ($from) {
                $message->from($from['address'], $from['name'])
                    ->to($this->email)
                    ->subject($this->subject)
                    ->html($this->bodyHtml);

                if ($this->bodyText) {
                    $message->text($this->bodyText);
                }
            });

            // Update notification queue status to sent
            if ($this->notificationQueueId) {
                $queueRepository = app(\Appsolutely\AIO\Repositories\NotificationQueueRepository::class);
                $queueRepository->updateStatus($this->notificationQueueId, 'sent');
            }

            Log::info('Notification email sent successfully', [
                'notification_id' => $this->notificationQueueId,
                'email'           => $this->email,
                'subject'         => $this->subject,
                'sender'          => $sender->name,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification email', [
                'notification_id' => $this->notificationQueueId,
                'email'           => $this->email,
                'subject'         => $this->subject,
                'error'           => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Resolve sender for this email
     */
    private function resolveSender(): NotificationSender
    {
        if ($this->senderId) {
            $sender = NotificationSender::find($this->senderId);
            if ($sender && $sender->is_active) {
                return $sender;
            }
        }

        // Fallback to default external sender
        $senderRepository = app(NotificationSenderRepository::class);
        $defaultSender    = $senderRepository->getDefaultForCategory('external');

        if (! $defaultSender) {
            throw new \Exception('No active sender available');
        }

        return $defaultSender;
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Update notification queue status to failed
        if ($this->notificationQueueId) {
            $queueRepository = app(\Appsolutely\AIO\Repositories\NotificationQueueRepository::class);
            $queueRepository->updateStatus(
                $this->notificationQueueId,
                'failed',
                $exception->getMessage()
            );
        }

        Log::error('SendNotificationEmail job failed', [
            'notification_id' => $this->notificationQueueId,
            'email'           => $this->email,
            'subject'         => $this->subject,
            'sender_id'       => $this->senderId,
            'error'           => $exception->getMessage(),
        ]);
    }
}
