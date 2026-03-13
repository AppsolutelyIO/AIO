<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Jobs;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Appsolutely\AIO\Jobs\SendNotificationEmail;
use Appsolutely\AIO\Models\NotificationQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class SendNotificationEmailTest extends TestCase
{
    use RefreshDatabase;

    // --- job configuration ---

    public function test_job_implements_should_queue(): void
    {
        $job = new SendNotificationEmail();

        $this->assertInstanceOf(ShouldQueue::class, $job);
    }

    public function test_job_has_correct_tries_value(): void
    {
        $job = new SendNotificationEmail();

        $this->assertEquals(3, $job->tries);
    }

    public function test_job_has_backoff_configured(): void
    {
        $job = new SendNotificationEmail();

        $this->assertGreaterThan(0, $job->backoff);
    }

    // --- failed ---

    public function test_failed_updates_notification_queue_status(): void
    {
        $queue = NotificationQueue::factory()->create(['status' => 'pending']);

        $job = new SendNotificationEmail(
            notificationQueueId: $queue->id,
            email: 'test@example.com',
            subject: 'Test',
            bodyHtml: '<p>test</p>'
        );

        $job->failed(new \Exception('SMTP connection refused'));

        $this->assertEquals(NotificationQueueStatus::Failed, $queue->fresh()->status);
    }

    public function test_failed_without_queue_id_logs_error(): void
    {
        $job = new SendNotificationEmail(
            notificationQueueId: null,
            email: 'test@example.com',
            subject: 'Test',
            bodyHtml: '<p>test</p>'
        );

        // Should not throw - the job logs via Log::error()
        \Illuminate\Support\Facades\Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message, array $context) {
                return str_contains($message, 'SendNotificationEmail')
                    && $context['notification_id'] === null;
            });

        $job->failed(new \Exception('Error'));

        // Mockery verifies the Log expectation; count it as a PHPUnit assertion
        $this->addToAssertionCount(1);
    }

    public function test_failed_stores_error_message_in_queue(): void
    {
        $queue = NotificationQueue::factory()->create(['status' => 'pending']);

        $job = new SendNotificationEmail(
            notificationQueueId: $queue->id,
            email: 'test@example.com',
            subject: 'Test',
            bodyHtml: '<p>test</p>'
        );

        $job->failed(new \Exception('SMTP connection refused'));

        $freshQueue = $queue->fresh();
        $this->assertEquals(NotificationQueueStatus::Failed, $freshQueue->status);
        $this->assertStringContainsString('SMTP connection refused', $freshQueue->error_message ?? '');
    }

    public function test_job_uses_notifications_queue(): void
    {
        $job = new SendNotificationEmail();

        $this->assertEquals('notifications', $job->queue);
    }
}
