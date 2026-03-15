<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\NotificationQueueStatus;
use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class NotificationQueueTest extends TestCase
{
    use RefreshDatabase;

    // --- status cast ---

    public function test_status_is_cast_to_enum(): void
    {
        $item = NotificationQueue::factory()->pending()->create();

        $this->assertInstanceOf(NotificationQueueStatus::class, $item->status);
        $this->assertSame(NotificationQueueStatus::Pending, $item->status);
    }

    // --- markAsSent ---

    public function test_mark_as_sent_updates_status_and_sent_at(): void
    {
        Carbon::setTestNow(now());

        $item = NotificationQueue::factory()->pending()->create([
            'sent_at' => null,
        ]);

        $item->markAsSent();
        $item->refresh();

        $this->assertSame(NotificationQueueStatus::Sent, $item->status);
        $this->assertNotNull($item->sent_at);
        $this->assertEqualsWithDelta(now()->timestamp, $item->sent_at->timestamp, 2);

        Carbon::setTestNow();
    }

    public function test_mark_as_sent_persists_to_database(): void
    {
        $item = NotificationQueue::factory()->pending()->create();

        $item->markAsSent();

        $this->assertDatabaseHas('notification_queue', [
            'id'     => $item->id,
            'status' => NotificationQueueStatus::Sent->value,
        ]);
    }

    // --- markAsFailed ---

    public function test_mark_as_failed_updates_status_error_and_increments_attempts(): void
    {
        $item = NotificationQueue::factory()->pending()->create([
            'attempts' => 0,
        ]);

        $item->markAsFailed('SMTP timeout');
        $item->refresh();

        $this->assertSame(NotificationQueueStatus::Failed, $item->status);
        $this->assertSame('SMTP timeout', $item->error_message);
        $this->assertSame(1, $item->attempts);
    }

    public function test_mark_as_failed_increments_existing_attempts(): void
    {
        $item = NotificationQueue::factory()->pending()->create([
            'attempts' => 2,
        ]);

        $item->markAsFailed('Connection refused');
        $item->refresh();

        $this->assertSame(3, $item->attempts);
    }

    public function test_mark_as_failed_persists_to_database(): void
    {
        $item = NotificationQueue::factory()->pending()->create();

        $item->markAsFailed('Server error');

        $this->assertDatabaseHas('notification_queue', [
            'id'            => $item->id,
            'status'        => NotificationQueueStatus::Failed->value,
            'error_message' => 'Server error',
        ]);
    }

    // --- retry ---

    public function test_retry_resets_status_to_pending_and_clears_error(): void
    {
        Carbon::setTestNow(now());

        $item = NotificationQueue::factory()->failed()->create();

        $item->retry();
        $item->refresh();

        $this->assertSame(NotificationQueueStatus::Pending, $item->status);
        $this->assertNull($item->error_message);
        $this->assertEqualsWithDelta(now()->timestamp, $item->scheduled_at->timestamp, 2);

        Carbon::setTestNow();
    }

    public function test_retry_preserves_attempts_count(): void
    {
        $item = NotificationQueue::factory()->failed()->create([
            'attempts' => 5,
        ]);

        $item->retry();
        $item->refresh();

        $this->assertSame(5, $item->attempts);
    }

    public function test_retry_persists_to_database(): void
    {
        $item = NotificationQueue::factory()->failed()->create();

        $item->retry();

        $this->assertDatabaseHas('notification_queue', [
            'id'            => $item->id,
            'status'        => NotificationQueueStatus::Pending->value,
            'error_message' => null,
        ]);
    }

    // --- is_ready_to_send attribute ---

    public function test_is_ready_to_send_returns_true_when_pending_and_scheduled_in_past(): void
    {
        $item = NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->assertTrue($item->is_ready_to_send);
    }

    public function test_is_ready_to_send_returns_true_when_pending_and_scheduled_at_now(): void
    {
        Carbon::setTestNow(now());

        $item = NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now(),
        ]);

        $this->assertTrue($item->is_ready_to_send);

        Carbon::setTestNow();
    }

    public function test_is_ready_to_send_returns_false_when_pending_and_scheduled_in_future(): void
    {
        $item = NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->addHour(),
        ]);

        $this->assertFalse($item->is_ready_to_send);
    }

    public function test_is_ready_to_send_returns_false_when_not_pending(): void
    {
        $item = NotificationQueue::factory()->sent()->create([
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->assertFalse($item->is_ready_to_send);
    }

    public function test_is_ready_to_send_returns_false_when_failed(): void
    {
        $item = NotificationQueue::factory()->failed()->create([
            'scheduled_at' => now()->subMinute(),
        ]);

        $this->assertFalse($item->is_ready_to_send);
    }

    // --- formatted_trigger_data attribute ---

    public function test_formatted_trigger_data_returns_no_data_when_empty(): void
    {
        $item = NotificationQueue::factory()->make([
            'trigger_data' => [],
        ]);

        $this->assertSame('No data', $item->formatted_trigger_data);
    }

    public function test_formatted_trigger_data_returns_no_data_when_null(): void
    {
        $item = NotificationQueue::factory()->make([
            'trigger_data' => null,
        ]);

        $this->assertSame('No data', $item->formatted_trigger_data);
    }

    public function test_formatted_trigger_data_formats_simple_key_value_pairs(): void
    {
        $item = NotificationQueue::factory()->make([
            'trigger_data' => [
                'name'  => 'John',
                'email' => 'john@example.com',
            ],
        ]);

        $this->assertSame('name: John, email: john@example.com', $item->formatted_trigger_data);
    }

    public function test_formatted_trigger_data_json_encodes_nested_arrays(): void
    {
        $item = NotificationQueue::factory()->make([
            'trigger_data' => [
                'user' => ['first' => 'John', 'last' => 'Doe'],
            ],
        ]);

        $this->assertSame('user: {"first":"John","last":"Doe"}', $item->formatted_trigger_data);
    }

    public function test_formatted_trigger_data_handles_mixed_values(): void
    {
        $item = NotificationQueue::factory()->make([
            'trigger_data' => [
                'name' => 'Jane',
                'tags' => ['vip', 'premium'],
            ],
        ]);

        $this->assertSame('name: Jane, tags: ["vip","premium"]', $item->formatted_trigger_data);
    }

    public function test_formatted_trigger_data_handles_null_and_integer_values(): void
    {
        $item = NotificationQueue::factory()->make([
            'trigger_data' => [
                'count'  => 42,
                'active' => true,
                'note'   => null,
            ],
        ]);

        $result = $item->formatted_trigger_data;

        $this->assertStringContainsString('count: 42', $result);
        $this->assertStringContainsString('active: 1', $result);
        $this->assertStringContainsString('note: ', $result);
    }

    // --- status_label attribute ---

    public function test_status_label_returns_label_for_pending(): void
    {
        $item = NotificationQueue::factory()->make([
            'status' => NotificationQueueStatus::Pending,
        ]);

        $this->assertSame(NotificationQueueStatus::Pending->label(), $item->status_label);
    }

    public function test_status_label_returns_label_for_sent(): void
    {
        $item = NotificationQueue::factory()->make([
            'status' => NotificationQueueStatus::Sent,
        ]);

        $this->assertSame(NotificationQueueStatus::Sent->label(), $item->status_label);
    }

    // --- status_color attribute ---

    public function test_status_color_returns_color_for_pending(): void
    {
        $item = NotificationQueue::factory()->make([
            'status' => NotificationQueueStatus::Pending,
        ]);

        $this->assertSame('warning', $item->status_color);
    }

    public function test_status_color_returns_color_for_failed(): void
    {
        $item = NotificationQueue::factory()->make([
            'status' => NotificationQueueStatus::Failed,
        ]);

        $this->assertSame('danger', $item->status_color);
    }

    // --- status_badge attribute ---

    public function test_status_badge_returns_html_span(): void
    {
        $item = NotificationQueue::factory()->make([
            'status' => NotificationQueueStatus::Sent,
        ]);

        $badge = $item->status_badge;

        $this->assertStringContainsString('<span', $badge);
        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('bg-success', $badge);
        $this->assertStringContainsString(NotificationQueueStatus::Sent->label(), $badge);
    }

    public function test_status_badge_contains_correct_color_for_failed(): void
    {
        $item = NotificationQueue::factory()->make([
            'status' => NotificationQueueStatus::Failed,
        ]);

        $this->assertStringContainsString('bg-danger', $item->status_badge);
    }

    // --- getStatuses ---

    public function test_get_statuses_returns_all_status_values(): void
    {
        $statuses = NotificationQueue::getStatuses();

        $this->assertIsArray($statuses);
        $this->assertContains('pending', $statuses);
        $this->assertContains('processing', $statuses);
        $this->assertContains('sent', $statuses);
        $this->assertContains('failed', $statuses);
        $this->assertContains('cancelled', $statuses);
        $this->assertCount(5, $statuses);
    }

    // --- getStatusLabels ---

    public function test_get_status_labels_returns_keyed_array(): void
    {
        $labels = NotificationQueue::getStatusLabels();

        $this->assertIsArray($labels);
        $this->assertArrayHasKey('pending', $labels);
        $this->assertArrayHasKey('sent', $labels);
        $this->assertArrayHasKey('failed', $labels);
        $this->assertArrayHasKey('processing', $labels);
        $this->assertArrayHasKey('cancelled', $labels);
        $this->assertCount(5, $labels);
    }

    // --- getStatusColors ---

    public function test_get_status_colors_returns_keyed_array(): void
    {
        $colors = NotificationQueue::getStatusColors();

        $this->assertIsArray($colors);
        $this->assertSame('warning', $colors['pending']);
        $this->assertSame('info', $colors['processing']);
        $this->assertSame('success', $colors['sent']);
        $this->assertSame('danger', $colors['failed']);
        $this->assertSame('secondary', $colors['cancelled']);
    }

    // --- scopePending ---

    public function test_scope_pending_filters_only_pending_items(): void
    {
        $pending = NotificationQueue::factory()->pending()->create();
        $sent    = NotificationQueue::factory()->sent()->create();
        $failed  = NotificationQueue::factory()->failed()->create();

        $results = NotificationQueue::pending()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($pending));
    }

    // --- scopeSent ---

    public function test_scope_sent_filters_only_sent_items(): void
    {
        NotificationQueue::factory()->pending()->create();
        $sent = NotificationQueue::factory()->sent()->create();
        NotificationQueue::factory()->failed()->create();

        $results = NotificationQueue::sent()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($sent));
    }

    // --- scopeFailed ---

    public function test_scope_failed_filters_only_failed_items(): void
    {
        NotificationQueue::factory()->pending()->create();
        NotificationQueue::factory()->sent()->create();
        $failed = NotificationQueue::factory()->failed()->create();

        $results = NotificationQueue::failed()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($failed));
    }

    // --- scopeReadyToSend ---

    public function test_scope_ready_to_send_filters_pending_with_past_scheduled_at(): void
    {
        $ready = NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->subMinute(),
        ]);
        NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now()->addHour(),
        ]);
        NotificationQueue::factory()->sent()->create([
            'scheduled_at' => now()->subMinute(),
        ]);

        $results = NotificationQueue::readyToSend()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($ready));
    }

    public function test_scope_ready_to_send_includes_items_scheduled_at_now(): void
    {
        Carbon::setTestNow(now());

        $ready = NotificationQueue::factory()->pending()->create([
            'scheduled_at' => now(),
        ]);

        $results = NotificationQueue::readyToSend()->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->first()->is($ready));

        Carbon::setTestNow();
    }

    public function test_scope_ready_to_send_excludes_non_pending_statuses(): void
    {
        NotificationQueue::factory()->sent()->create([
            'scheduled_at' => now()->subMinute(),
        ]);
        NotificationQueue::factory()->failed()->create([
            'scheduled_at' => now()->subMinute(),
        ]);
        NotificationQueue::factory()->create([
            'status'       => NotificationQueueStatus::Processing,
            'scheduled_at' => now()->subMinute(),
        ]);
        NotificationQueue::factory()->create([
            'status'       => NotificationQueueStatus::Cancelled,
            'scheduled_at' => now()->subMinute(),
        ]);

        $results = NotificationQueue::readyToSend()->get();

        $this->assertCount(0, $results);
    }
}
