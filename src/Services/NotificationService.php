<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Exceptions\NotFoundException;
use Appsolutely\AIO\Exceptions\NotificationTemplateNotFoundException;
use Appsolutely\AIO\Jobs\SendNotificationEmail;
use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Repositories\NotificationQueueRepository;
use Appsolutely\AIO\Repositories\NotificationRuleRepository;
use Appsolutely\AIO\Repositories\NotificationTemplateRepository;
use Appsolutely\AIO\Services\Contracts\NotificationQueueServiceInterface;
use Appsolutely\AIO\Services\Contracts\NotificationRuleServiceInterface;
use Appsolutely\AIO\Services\Contracts\NotificationServiceInterface;
use Appsolutely\AIO\Services\Contracts\NotificationTemplateServiceInterface;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\MailException;
use Illuminate\Queue\MaxAttemptsExceededException;
use Psr\Log\LoggerInterface;

final class NotificationService implements NotificationServiceInterface
{
    public static array $processedTriggers = [];

    public function __construct(
        private readonly NotificationTemplateRepository $templateRepository,
        private readonly NotificationRuleRepository $ruleRepository,
        private readonly NotificationQueueRepository $queueRepository,
        private readonly NotificationTemplateServiceInterface $templateService,
        private readonly NotificationRuleServiceInterface $ruleService,
        private readonly NotificationQueueServiceInterface $queueService,
        private readonly NotificationSenderService $senderService,
        private readonly LoggerInterface $logger,
        private readonly Mailer $mailer
    ) {}

    public function resetProcessedTriggers(): void
    {
        self::$processedTriggers = [];
    }

    public static function resetCache(): void
    {
        self::$processedTriggers = [];
    }

    public function trigger(string $triggerType, string $reference, array $data): void
    {
        $triggerKey = $triggerType . ':' . $reference . ':' . ($data['entry_id'] ?? 'unknown');

        if (isset(self::$processedTriggers[$triggerKey])) {
            $this->logger->warning('Notification: duplicate trigger skipped', [
                'trigger'  => $triggerType . ':' . $reference,
                'entry_id' => $data['entry_id'] ?? null,
            ]);

            return;
        }

        self::$processedTriggers[$triggerKey] = true;

        try {
            $rules = $this->ruleRepository->findByTrigger($triggerType, $reference);

            foreach ($rules as $rule) {
                if ($this->ruleService->evaluateConditions($rule, $data)) {
                    $this->processRule($rule, $data);
                }
            }
        } catch (NotFoundException $e) {
            $this->logger->warning('Failed to trigger notifications: resource not found', [
                'trigger_type' => $triggerType,
                'reference'    => $reference,
                'error'        => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to trigger notifications: unexpected error', [
                'trigger_type' => $triggerType,
                'reference'    => $reference,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    public function sendImmediate(string $templateSlug, string $email, array $data, ?int $senderId = null): bool
    {
        try {
            $template = $this->templateRepository->findBySlug($templateSlug);

            if (! $template) {
                $this->logger->warning("Template not found: {$templateSlug}");

                return false;
            }

            $rendered = $template->render($data);

            dispatch(new SendNotificationEmail(
                notificationQueueId: null,
                email: $email,
                subject: $rendered['subject'],
                bodyHtml: $rendered['body_html'],
                bodyText: $rendered['body_text'],
                senderId: $senderId
            ));

            return true;
        } catch (NotFoundException $e) {
            $this->logger->warning('Failed to dispatch notification email: template not found', [
                'template_slug' => $templateSlug,
                'email'         => $email,
                'error'         => $e->getMessage(),
            ]);

            return false;
        } catch (MaxAttemptsExceededException $e) {
            $this->logger->error('Failed to dispatch notification email: queue max attempts exceeded', [
                'template_slug' => $templateSlug,
                'email'         => $email,
                'error'         => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Failed to dispatch notification email: unexpected error', [
                'template_slug' => $templateSlug,
                'email'         => $email,
                'error'         => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function schedule(string $templateSlug, string $email, array $data, \Carbon\Carbon $when): NotificationQueue
    {
        $template = $this->templateRepository->findBySlug($templateSlug);
        if (! $template) {
            throw new NotificationTemplateNotFoundException($templateSlug);
        }

        $rendered = $template->render($data);

        return $this->queueRepository->createQueueItem([
            'template_id'     => $template->id,
            'recipient_email' => $email,
            'subject'         => $rendered['subject'],
            'body_html'       => $rendered['body_html'],
            'body_text'       => $rendered['body_text'],
            'variables'       => $data,
            'scheduled_at'    => $when,
            'status'          => 'pending',
        ]);
    }

    public function processPendingNotifications(): int
    {
        return $this->queueService->processPending(100);
    }

    public function processQueue(): int
    {
        return $this->processPendingNotifications();
    }

    public function getStatistics(): array
    {
        $queueStats = $this->queueRepository->getStatistics();

        return [
            'templates_count'       => $this->templateRepository->getActive()->count(),
            'rules_count'           => $this->ruleRepository->getActiveWithTemplates()->count(),
            'pending_notifications' => $queueStats['pending'],
            'sent_today'            => $queueStats['today'],
            'failed_today'          => $queueStats['failed'],
        ];
    }

    protected function processRule(NotificationRule $rule, array $data): void
    {
        $recipients  = $this->ruleService->getRecipients($rule, $data);
        $scheduledAt = $rule->getScheduledAt();
        $sender      = $this->senderService->getSenderForRule($rule, $data);

        $recipients = array_unique($recipients);

        foreach ($recipients as $email) {
            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $rendered = $rule->template->render($data);

            $queueItemData = [
                'rule_id'         => $rule->id,
                'template_id'     => $rule->template_id,
                'sender_id'       => $sender?->id,
                'recipient_email' => $email,
                'subject'         => $rendered['subject'],
                'body_html'       => $rendered['body_html'],
                'body_text'       => $rendered['body_text'],
                'trigger_data'    => $data,
                'scheduled_at'    => $scheduledAt,
                'status'          => 'pending',
            ];

            if (isset($data['entry_id'])) {
                $queueItemData['form_entry_id'] = $data['entry_id'];
            }

            $this->queueRepository->createQueueItem($queueItemData);
        }
    }

    protected function sendEmail(string $email, string $subject, string $bodyHtml, ?string $bodyText = null): bool
    {
        try {
            $this->mailer->send([], [], function ($message) use ($email, $subject, $bodyHtml, $bodyText) {
                $message->to($email)
                    ->subject($subject)
                    ->html($bodyHtml);

                if ($bodyText) {
                    $message->text($bodyText);
                }
            });

            return true;
        } catch (MailException $e) {
            $this->logger->error('Email sending failed: mail error', [
                'email'   => $email,
                'subject' => $subject,
                'error'   => $e->getMessage(),
            ]);

            return false;
        } catch (\Exception $e) {
            $this->logger->error('Email sending failed: unexpected error', [
                'email'   => $email,
                'subject' => $subject,
                'error'   => $e->getMessage(),
            ]);

            return false;
        }
    }
}
