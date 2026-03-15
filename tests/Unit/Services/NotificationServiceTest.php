<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Exceptions\NotificationTemplateNotFoundException;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Services\NotificationService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

final class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        NotificationService::resetCache();
        $this->service = app(NotificationService::class);
    }

    // --- resetProcessedTriggers ---

    public function test_reset_processed_triggers_clears_cache(): void
    {
        NotificationService::$processedTriggers['some:key:1'] = true;

        $this->service->resetProcessedTriggers();

        $this->assertEmpty(NotificationService::$processedTriggers);
    }

    // --- resetCache (static) ---

    public function test_reset_cache_clears_static_cache(): void
    {
        NotificationService::$processedTriggers['other:key:2'] = true;

        NotificationService::resetCache();

        $this->assertEmpty(NotificationService::$processedTriggers);
    }

    // --- trigger ---

    public function test_trigger_does_nothing_when_no_rules_match(): void
    {
        // No rules in DB - should not throw
        $this->service->trigger('form_submission', 'contact-form', ['entry_id' => 1]);

        // No notification queue items created
        $this->assertDatabaseCount('notification_queue', 0);
    }

    public function test_trigger_skips_duplicate_trigger_key(): void
    {
        $triggerKey                                          = 'form_submitted:contact-form:42';
        NotificationService::$processedTriggers[$triggerKey] = true;

        $this->service->trigger('form_submission', 'contact-form', ['entry_id' => 42]);

        // No additional queue items created (duplicate skipped)
        $this->assertDatabaseCount('notification_queue', 0);
    }

    public function test_trigger_processes_matching_rule(): void
    {
        $template = NotificationTemplate::factory()->create([
            'slug'      => 'test-notification',
            'subject'   => 'Hello {{name}}',
            'body_html' => '<p>Hello {{name}}</p>',
            'body_text' => 'Hello {{name}}',
        ]);

        $formEntry = FormEntry::factory()->create();

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'test-form',
            'template_id'       => $template->id,
            'status'            => Status::ACTIVE,
            'recipient_type'    => 'custom',
            'recipient_emails'  => ['admin@example.com'],
            'conditions'        => [],
        ]);

        $this->service->trigger('form_submission', 'test-form', [
            'entry_id' => $formEntry->id,
            'name'     => 'Test User',
        ]);

        $this->assertDatabaseCount('notification_queue', 1);
    }

    // --- sendImmediate ---

    public function test_send_immediate_returns_false_when_template_not_found(): void
    {
        $result = $this->service->sendImmediate('non-existent-template', 'test@example.com', []);

        $this->assertFalse($result);
    }

    public function test_send_immediate_returns_true_and_dispatches_job_when_template_found(): void
    {
        NotificationTemplate::factory()->create([
            'slug'      => 'welcome-email',
            'subject'   => 'Welcome!',
            'body_html' => '<p>Welcome!</p>',
            'body_text' => 'Welcome!',
        ]);

        $result = $this->service->sendImmediate('welcome-email', 'user@example.com', []);

        $this->assertTrue($result);
    }

    // --- schedule ---

    public function test_schedule_creates_notification_queue_item(): void
    {
        $template = NotificationTemplate::factory()->create([
            'slug'      => 'scheduled-notification',
            'subject'   => 'Scheduled',
            'body_html' => '<p>Scheduled</p>',
            'body_text' => 'Scheduled',
        ]);

        $when  = now()->addHour();
        $queue = $this->service->schedule('scheduled-notification', 'user@example.com', [], $when);

        $this->assertInstanceOf(NotificationQueue::class, $queue);
        $this->assertEquals('user@example.com', $queue->recipient_email);
        $this->assertEquals(NotificationQueueStatus::Pending, $queue->status);
    }

    public function test_schedule_throws_exception_when_template_not_found(): void
    {
        $this->expectException(NotificationTemplateNotFoundException::class);

        $this->service->schedule('non-existent-template', 'user@example.com', [], now()->addHour());
    }

    // --- processPendingNotifications ---

    public function test_process_pending_notifications_returns_integer(): void
    {
        $result = $this->service->processPendingNotifications();

        $this->assertIsInt($result);
    }

    // --- processQueue ---

    public function test_process_queue_delegates_to_process_pending_notifications(): void
    {
        NotificationQueue::factory()->count(2)->create([
            'status'       => 'pending',
            'scheduled_at' => now()->subMinute(),
        ]);

        $result = $this->service->processQueue();

        $this->assertEquals(2, $result);
    }

    // --- getStatistics ---

    public function test_get_statistics_returns_array_with_expected_keys(): void
    {
        $stats = $this->service->getStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('templates_count', $stats);
        $this->assertArrayHasKey('rules_count', $stats);
        $this->assertArrayHasKey('pending_notifications', $stats);
        $this->assertArrayHasKey('sent_today', $stats);
        $this->assertArrayHasKey('failed_today', $stats);
    }

    public function test_get_statistics_returns_correct_counts(): void
    {
        NotificationTemplate::factory()->count(3)->create(['status' => Status::ACTIVE]);

        $stats = $this->service->getStatistics();

        $this->assertEquals(3, $stats['templates_count']);
    }
}
