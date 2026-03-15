<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Services\NotificationQueueService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

final class NotificationQueueServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationQueueService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->service = app(NotificationQueueService::class);
    }

    // --- processPending ---

    public function test_process_pending_returns_zero_when_no_pending_notifications(): void
    {
        $processed = $this->service->processPending();

        $this->assertEquals(0, $processed);
    }

    public function test_process_pending_dispatches_jobs_for_pending_notifications(): void
    {
        NotificationQueue::factory()->count(3)->create([
            'status'       => 'pending',
            'scheduled_at' => now()->subMinute(),
        ]);

        $processed = $this->service->processPending();

        $this->assertEquals(3, $processed);
    }

    public function test_process_pending_respects_limit(): void
    {
        NotificationQueue::factory()->count(10)->create([
            'status'       => 'pending',
            'scheduled_at' => now()->subMinute(),
        ]);

        $processed = $this->service->processPending(5);

        $this->assertEquals(5, $processed);
    }

    public function test_process_pending_marks_notifications_as_processing(): void
    {
        $notification = NotificationQueue::factory()->create([
            'status'       => 'pending',
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->service->processPending(1);

        $this->assertDatabaseHas('notification_queue', [
            'id'     => $notification->id,
            'status' => 'processing',
        ]);
    }

    public function test_process_pending_does_not_process_non_pending_notifications(): void
    {
        NotificationQueue::factory()->create([
            'status'       => 'sent',
            'scheduled_at' => now()->subMinute(),
        ]);

        $processed = $this->service->processPending();

        $this->assertEquals(0, $processed);
    }
}
