<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\NotificationQueue;
use Carbon\Carbon;

interface NotificationServiceInterface
{
    /**
     * Trigger notifications based on event
     */
    public function trigger(string $triggerType, string $reference, array $data): void;

    /**
     * Send notification immediately
     */
    public function sendImmediate(string $templateSlug, string $email, array $data): bool;

    /**
     * Schedule notification for later
     */
    public function schedule(string $templateSlug, string $email, array $data, Carbon $when): NotificationQueue;

    /**
     * Process pending notifications
     */
    public function processPendingNotifications(): int;

    /**
     * Process queue
     */
    public function processQueue(): int;

    /**
     * Get notification statistics
     */
    public function getStatistics(): array;

    /**
     * Reset the processed triggers cache (useful for testing)
     */
    public function resetProcessedTriggers(): void;
}
