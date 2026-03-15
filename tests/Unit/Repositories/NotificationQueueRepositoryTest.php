<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Models\NotificationRule;
use Appsolutely\AIO\Models\NotificationTemplate;
use Appsolutely\AIO\Repositories\NotificationQueueRepository;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

final class NotificationQueueRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private NotificationQueueRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(NotificationQueueRepository::class);
    }

    public function test_has_notification_for_entry_and_rule_returns_true_when_exists(): void
    {
        $entry = FormEntry::factory()->create();
        $rule  = NotificationRule::factory()->create();

        NotificationQueue::factory()->create([
            'form_entry_id' => $entry->id,
            'rule_id'       => $rule->id,
        ]);

        $result = $this->repository->hasNotificationForEntryAndRule($entry->id, $rule->id);

        $this->assertTrue($result);
    }

    public function test_has_notification_for_entry_and_rule_returns_false_when_not_exists(): void
    {
        $entry = FormEntry::factory()->create();
        $rule  = NotificationRule::factory()->create();

        $result = $this->repository->hasNotificationForEntryAndRule($entry->id, $rule->id);

        $this->assertFalse($result);
    }

    public function test_has_notification_for_entry_and_rule_checks_specific_combination(): void
    {
        $entry1 = FormEntry::factory()->create();
        $entry2 = FormEntry::factory()->create();
        $rule1  = NotificationRule::factory()->create();
        $rule2  = NotificationRule::factory()->create();

        // Create notification for entry1 + rule1
        NotificationQueue::factory()->create([
            'form_entry_id' => $entry1->id,
            'rule_id'       => $rule1->id,
        ]);

        // Should return true for entry1 + rule1
        $this->assertTrue($this->repository->hasNotificationForEntryAndRule($entry1->id, $rule1->id));

        // Should return false for other combinations
        $this->assertFalse($this->repository->hasNotificationForEntryAndRule($entry1->id, $rule2->id));
        $this->assertFalse($this->repository->hasNotificationForEntryAndRule($entry2->id, $rule1->id));
        $this->assertFalse($this->repository->hasNotificationForEntryAndRule($entry2->id, $rule2->id));
    }

    public function test_count_for_entry_and_rule_returns_correct_count(): void
    {
        $entry = FormEntry::factory()->create();
        $rule  = NotificationRule::factory()->create();

        // Create multiple notifications for same entry and rule
        NotificationQueue::factory()->count(3)->create([
            'form_entry_id' => $entry->id,
            'rule_id'       => $rule->id,
        ]);

        $result = $this->repository->countForEntryAndRule($entry->id, $rule->id);

        $this->assertEquals(3, $result);
    }

    public function test_count_for_entry_and_rule_returns_zero_when_no_notifications(): void
    {
        $entry = FormEntry::factory()->create();
        $rule  = NotificationRule::factory()->create();

        $result = $this->repository->countForEntryAndRule($entry->id, $rule->id);

        $this->assertEquals(0, $result);
    }

    public function test_count_for_entry_and_rule_only_counts_specific_combination(): void
    {
        $entry = FormEntry::factory()->create();
        $rule1 = NotificationRule::factory()->create();
        $rule2 = NotificationRule::factory()->create();

        // Create 2 notifications for rule1
        NotificationQueue::factory()->count(2)->create([
            'form_entry_id' => $entry->id,
            'rule_id'       => $rule1->id,
        ]);

        // Create 3 notifications for rule2
        NotificationQueue::factory()->count(3)->create([
            'form_entry_id' => $entry->id,
            'rule_id'       => $rule2->id,
        ]);

        $this->assertEquals(2, $this->repository->countForEntryAndRule($entry->id, $rule1->id));
        $this->assertEquals(3, $this->repository->countForEntryAndRule($entry->id, $rule2->id));
    }

    public function test_create_queue_item_with_form_entry_id(): void
    {
        $entry    = FormEntry::factory()->create();
        $rule     = NotificationRule::factory()->create();
        $template = NotificationTemplate::factory()->create();

        $queueItem = $this->repository->createQueueItem([
            'rule_id'         => $rule->id,
            'template_id'     => $template->id,
            'form_entry_id'   => $entry->id,
            'recipient_email' => 'test@example.com',
            'subject'         => 'Test Subject',
            'body_html'       => '<p>Test</p>',
            'body_text'       => 'Test',
            'trigger_data'    => ['test' => 'data'],
            'status'          => 'pending',
            'scheduled_at'    => now(),
        ]);

        $this->assertInstanceOf(NotificationQueue::class, $queueItem);
        $this->assertEquals($entry->id, $queueItem->form_entry_id);
        $this->assertEquals($rule->id, $queueItem->rule_id);
        $this->assertEquals('test@example.com', $queueItem->recipient_email);
        $this->assertEquals(NotificationQueueStatus::Pending, $queueItem->status);
    }

    public function test_queue_item_can_be_created_without_form_entry_id(): void
    {
        $rule     = NotificationRule::factory()->create();
        $template = NotificationTemplate::factory()->create();

        $queueItem = $this->repository->createQueueItem([
            'rule_id'         => $rule->id,
            'template_id'     => $template->id,
            'recipient_email' => 'test@example.com',
            'subject'         => 'Test Subject',
            'body_html'       => '<p>Test</p>',
            'body_text'       => 'Test',
            'trigger_data'    => ['test' => 'data'],
            'status'          => 'pending',
            'scheduled_at'    => now(),
        ]);

        $this->assertInstanceOf(NotificationQueue::class, $queueItem);
        $this->assertNull($queueItem->form_entry_id);
    }

    public function test_form_entry_id_is_nullable_on_delete(): void
    {
        $entry = FormEntry::factory()->create();
        $rule  = NotificationRule::factory()->create();

        $queueItem = NotificationQueue::factory()->create([
            'form_entry_id' => $entry->id,
            'rule_id'       => $rule->id,
        ]);

        // Force delete the form entry (not soft delete)
        $entry->forceDelete();

        // Refresh queue item from database
        $queueItem->refresh();

        // form_entry_id should be set to null due to onDelete('set null')
        $this->assertNull($queueItem->form_entry_id);
    }

    public function test_get_pending_to_send_returns_items_scheduled_in_the_past(): void
    {
        $ready = NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->subMinute(),
            'attempts'     => 0,
        ]);

        // Future scheduled - should not be returned
        NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->addHour(),
        ]);

        // Already sent - should not be returned
        NotificationQueue::factory()->sent()->create();

        $result = $this->repository->getPendingToSend();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($ready));
    }

    public function test_get_pending_to_send_excludes_items_at_max_retries(): void
    {
        config(['notifications.max_retry_attempts' => 3]);

        NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->subMinute(),
            'attempts'     => 3,
        ]);

        $result = $this->repository->getPendingToSend();

        $this->assertCount(0, $result);
    }

    public function test_get_retryable_returns_failed_items_under_max_retries(): void
    {
        config(['notifications.max_retry_attempts' => 3]);

        $retryable = NotificationQueue::factory()->create([
            'status'   => NotificationQueueStatus::Failed,
            'attempts' => 1,
        ]);

        // At max retries - should not be returned
        NotificationQueue::factory()->create([
            'status'   => NotificationQueueStatus::Failed,
            'attempts' => 3,
        ]);

        // Pending - should not be returned
        NotificationQueue::factory()->pending()->create();

        $result = $this->repository->getRetryable();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($retryable));
    }

    public function test_get_by_status_returns_paginated_results(): void
    {
        NotificationQueue::factory()->count(3)->sent()->create();
        NotificationQueue::factory()->pending()->create();

        $result = $this->repository->getByStatus(NotificationQueueStatus::Sent->value, 2);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(3, $result->total());
        $this->assertCount(2, $result->items());
    }

    public function test_update_status_sets_sent_at_when_sent(): void
    {
        $item = NotificationQueue::factory()->pending()->create();

        $result = $this->repository->updateStatus($item->id, NotificationQueueStatus::Sent->value);

        $this->assertEquals(NotificationQueueStatus::Sent->value, $result->status->value);
        $this->assertNotNull($result->sent_at);
    }

    public function test_update_status_sets_error_message_when_failed(): void
    {
        $item = NotificationQueue::factory()->pending()->create();

        $result = $this->repository->updateStatus($item->id, NotificationQueueStatus::Failed->value, 'SMTP timeout');

        $this->assertEquals(NotificationQueueStatus::Failed->value, $result->status->value);
        $this->assertEquals('SMTP timeout', $result->error_message);
    }

    public function test_update_status_without_error_message(): void
    {
        $item = NotificationQueue::factory()->pending()->create();

        $result = $this->repository->updateStatus($item->id, NotificationQueueStatus::Failed->value);

        $this->assertEquals(NotificationQueueStatus::Failed->value, $result->status->value);
        $this->assertNull($result->error_message);
    }

    public function test_increment_retry_increments_attempts(): void
    {
        config(['notifications.max_retry_attempts' => 3]);

        $item = NotificationQueue::factory()->create([
            'status'   => NotificationQueueStatus::Pending,
            'attempts' => 0,
        ]);

        $result = $this->repository->incrementRetry($item->id);

        $this->assertEquals(1, $result->attempts);
        $this->assertEquals(NotificationQueueStatus::Pending, $result->status);
    }

    public function test_increment_retry_sets_failed_at_max_retries(): void
    {
        config(['notifications.max_retry_attempts' => 3]);

        $item = NotificationQueue::factory()->create([
            'status'   => NotificationQueueStatus::Pending,
            'attempts' => 2,
        ]);

        $result = $this->repository->incrementRetry($item->id);

        $this->assertEquals(3, $result->attempts);
        $this->assertEquals(NotificationQueueStatus::Failed, $result->status);
    }

    public function test_get_statistics_returns_correct_counts(): void
    {
        NotificationQueue::factory()->count(2)->pending()->create();
        NotificationQueue::factory()->count(3)->sent()->create();
        NotificationQueue::factory()->failed()->create();

        $stats = $this->repository->getStatistics();

        $this->assertEquals(6, $stats['total']);
        $this->assertEquals(2, $stats['pending']);
        $this->assertEquals(3, $stats['sent']);
        $this->assertEquals(1, $stats['failed']);
        $this->assertEquals(6, $stats['today']);
        $this->assertArrayHasKey('this_week', $stats);
        $this->assertArrayHasKey('this_month', $stats);
    }

    public function test_get_scheduled_returns_future_pending_items(): void
    {
        $future = NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->addHour(),
        ]);

        // Past scheduled - should not be returned
        NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->subMinute(),
        ]);

        // Future but sent - should not be returned
        NotificationQueue::factory()->sent()->create([
            'scheduled_at' => now()->addHour(),
        ]);

        $result = $this->repository->getScheduled();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($future));
    }

    public function test_cancel_scheduled_cancels_pending_items_by_ids(): void
    {
        $pending1 = NotificationQueue::factory()->pending()->create();
        $pending2 = NotificationQueue::factory()->pending()->create();
        $sent     = NotificationQueue::factory()->sent()->create();

        $count = $this->repository->cancelScheduled([$pending1->id, $pending2->id, $sent->id]);

        $this->assertEquals(2, $count);
        $this->assertEquals(NotificationQueueStatus::Cancelled, $pending1->fresh()->status);
        $this->assertEquals(NotificationQueueStatus::Cancelled, $pending2->fresh()->status);
        $this->assertEquals(NotificationQueueStatus::Sent, $sent->fresh()->status);
    }

    public function test_retry_failed_resets_failed_items_to_pending(): void
    {
        config(['notifications.max_retry_attempts' => 3]);

        $failed1 = NotificationQueue::factory()->create([
            'status'        => NotificationQueueStatus::Failed,
            'attempts'      => 1,
            'error_message' => 'Some error',
        ]);

        // At max retries - should not be retried
        $failed2 = NotificationQueue::factory()->failed()->create([
            'attempts' => 3,
        ]);

        $count = $this->repository->retryFailed([$failed1->id, $failed2->id]);

        $this->assertEquals(1, $count);
        $this->assertEquals(NotificationQueueStatus::Pending, $failed1->fresh()->status);
        $this->assertNull($failed1->fresh()->error_message);
        $this->assertEquals(NotificationQueueStatus::Failed, $failed2->fresh()->status);
    }

    public function test_retry_specific_failed_notification(): void
    {
        config(['notifications.max_retry_attempts' => 3]);

        $failed = NotificationQueue::factory()->create([
            'status'        => NotificationQueueStatus::Failed,
            'attempts'      => 1,
            'error_message' => 'Timeout',
        ]);

        $result = $this->repository->retry($failed->id);

        $this->assertTrue($result);
        $this->assertEquals(NotificationQueueStatus::Pending, $failed->fresh()->status);
        $this->assertNull($failed->fresh()->error_message);
    }

    public function test_retry_returns_false_for_non_failed_notification(): void
    {
        $pending = NotificationQueue::factory()->pending()->create();

        $result = $this->repository->retry($pending->id);

        $this->assertFalse($result);
    }

    public function test_retry_returns_false_when_at_max_retries(): void
    {
        config(['notifications.max_retry_attempts' => 3]);

        $failed = NotificationQueue::factory()->failed()->create([
            'attempts' => 3,
        ]);

        $result = $this->repository->retry($failed->id);

        $this->assertFalse($result);
    }

    public function test_cancel_specific_pending_notification(): void
    {
        $pending = NotificationQueue::factory()->pending()->create();

        $result = $this->repository->cancel($pending->id);

        $this->assertTrue($result);
        $this->assertEquals(NotificationQueueStatus::Cancelled, $pending->fresh()->status);
    }

    public function test_cancel_specific_processing_notification(): void
    {
        $processing = NotificationQueue::factory()->create([
            'status' => NotificationQueueStatus::Processing,
        ]);

        $result = $this->repository->cancel($processing->id);

        $this->assertTrue($result);
        $this->assertEquals(NotificationQueueStatus::Cancelled, $processing->fresh()->status);
    }

    public function test_cancel_returns_false_for_sent_notification(): void
    {
        $sent = NotificationQueue::factory()->sent()->create();

        $result = $this->repository->cancel($sent->id);

        $this->assertFalse($result);
        $this->assertEquals(NotificationQueueStatus::Sent, $sent->fresh()->status);
    }

    public function test_clean_old_sent_deletes_old_sent_items(): void
    {
        $old = NotificationQueue::factory()->sent()->create([
            'sent_at' => now()->subDays(100),
        ]);

        $recent = NotificationQueue::factory()->sent()->create([
            'sent_at' => now()->subDays(10),
        ]);

        $count = $this->repository->cleanOldSent(90);

        $this->assertEquals(1, $count);
        $this->assertDatabaseMissing('notification_queue', ['id' => $old->id]);
        $this->assertDatabaseHas('notification_queue', ['id' => $recent->id]);
    }

    public function test_clean_old_sent_does_not_delete_non_sent_items(): void
    {
        NotificationQueue::factory()->pending()->create([
            'created_at' => now()->subDays(100),
        ]);

        $count = $this->repository->cleanOldSent(90);

        $this->assertEquals(0, $count);
    }

    public function test_get_recent_activity_returns_limited_items(): void
    {
        NotificationQueue::factory()->count(5)->create();

        $result = $this->repository->getRecentActivity(3);

        $this->assertCount(3, $result);
    }

    public function test_get_recent_activity_orders_by_updated_at_desc(): void
    {
        $older = NotificationQueue::factory()->create(['updated_at' => now()->subHour()]);
        $newer = NotificationQueue::factory()->create(['updated_at' => now()]);

        $result = $this->repository->getRecentActivity(10);

        $this->assertTrue($result->first()->is($newer));
        $this->assertTrue($result->last()->is($older));
    }

    public function test_get_by_template_returns_items_for_template(): void
    {
        $template = NotificationTemplate::factory()->create();
        $other    = NotificationTemplate::factory()->create();

        NotificationQueue::factory()->count(2)->create(['template_id' => $template->id]);
        NotificationQueue::factory()->create(['template_id' => $other->id]);

        $result = $this->repository->getByTemplate($template->id);

        $this->assertCount(2, $result);
        $result->each(function (NotificationQueue $item) use ($template): void {
            $this->assertEquals($template->id, $item->template_id);
        });
    }

    public function test_get_by_rule_returns_items_for_rule(): void
    {
        $rule  = NotificationRule::factory()->create();
        $other = NotificationRule::factory()->create();

        NotificationQueue::factory()->count(2)->create(['rule_id' => $rule->id]);
        NotificationQueue::factory()->create(['rule_id' => $other->id]);

        $result = $this->repository->getByRule($rule->id);

        $this->assertCount(2, $result);
        $result->each(function (NotificationQueue $item) use ($rule): void {
            $this->assertEquals($rule->id, $item->rule_id);
        });
    }
}
