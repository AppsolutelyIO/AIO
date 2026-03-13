<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Listeners;

use Appsolutely\AIO\Events\FormSubmitted;
use Appsolutely\AIO\Services\Contracts\NotificationServiceInterface;
use Appsolutely\AIO\Services\FormFieldFormatterService;

/**
 * Listener to trigger notifications when a form is submitted
 *
 * Handles notification triggering for form submissions by delegating
 * to the NotificationService.
 *
 * Note: This listener is synchronous for immediate execution.
 * If you need queued execution, implement ShouldQueue interface.
 */
final class TriggerFormNotifications
{
    public function __construct(
        private readonly NotificationServiceInterface $notificationService,
        private readonly FormFieldFormatterService $fieldFormatter
    ) {}

    /**
     * Handle the form submitted event
     */
    public function handle(FormSubmitted $event): void
    {
        // Use atomic lock to prevent duplicate processing across instances
        $lockKey = 'form_notification_processing:' . $event->entry->id . '_' . $event->form->id;

        $lock = \Cache::lock($lockKey, 10);

        if (! $lock->get()) {
            \Log::warning('Form notification: duplicate event skipped', [
                'entry_id'  => $event->entry->id,
                'form_slug' => $event->form->slug,
            ]);

            return;
        }

        try {
            // Load form fields relationship if not already loaded
            if (! $event->form->relationLoaded('fields')) {
                $event->form->load('fields');
            }

            // Prepare notification data using formatter service
            $notificationData = $this->fieldFormatter->prepareNotificationData($event->form, $event->entry);

            // Trigger notifications using 'form_submission' (matching the trigger type used in rules)
            // Use form slug as reference, or '*' for wildcard rules
            $this->notificationService->trigger(
                'form_submission',
                $event->form->slug,
                $notificationData
            );
        } finally {
            // Release lock after processing (or on exception)
            $lock->release();
        }
    }
}
