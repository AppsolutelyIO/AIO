<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Jobs\SendNotificationEmail;
use Appsolutely\AIO\Repositories\NotificationQueueRepository;
use Appsolutely\AIO\Services\Contracts\NotificationQueueServiceInterface;
use Illuminate\Queue\MaxAttemptsExceededException;
use Psr\Log\LoggerInterface;

final readonly class NotificationQueueService implements NotificationQueueServiceInterface
{
    public function __construct(
        protected NotificationQueueRepository $queueRepository,
        protected LoggerInterface $logger
    ) {}

    /**
     * Process pending notifications
     */
    public function processPending(int $limit = 100): int
    {
        $processed     = 0;
        $notifications = $this->queueRepository->getPendingToSend()->take($limit);

        foreach ($notifications as $notification) {
            try {
                // Dispatch individual email job for better queue management
                // Pass notification ID so the job can update status after sending
                dispatch(new SendNotificationEmail(
                    $notification->id,
                    $notification->recipient_email,
                    $notification->subject,
                    $notification->body_html,
                    $notification->body_text,
                    $notification->sender_id
                ));

                // Mark as processing (not sent - the job will mark it as sent)
                $this->queueRepository->updateStatus($notification->id, 'processing');
                $processed++;
            } catch (MaxAttemptsExceededException $e) {
                $this->queueRepository->updateStatus($notification->id, 'failed', $e->getMessage());
                $this->logger->error('Failed to dispatch queued notification: max attempts exceeded', [
                    'notification_id' => $notification->id,
                    'error'           => $e->getMessage(),
                ]);
            } catch (\Exception $e) {
                $this->queueRepository->updateStatus($notification->id, 'failed', $e->getMessage());
                $this->logger->error('Failed to dispatch queued notification: unexpected error', [
                    'notification_id' => $notification->id,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        return $processed;
    }
}
