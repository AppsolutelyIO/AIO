<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\NotificationQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class FormEntryNotificationRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_entry_has_many_notifications(): void
    {
        $form  = Form::factory()->create();
        $entry = FormEntry::factory()->create(['form_id' => $form->id]);

        $notification1 = NotificationQueue::factory()->create(['form_entry_id' => $entry->id]);
        $notification2 = NotificationQueue::factory()->create(['form_entry_id' => $entry->id]);
        $notification3 = NotificationQueue::factory()->create(['form_entry_id' => $entry->id]);

        $notifications = $entry->notifications;

        $this->assertCount(3, $notifications);
        $this->assertTrue($notifications->contains($notification1));
        $this->assertTrue($notifications->contains($notification2));
        $this->assertTrue($notifications->contains($notification3));
    }

    public function test_notification_queue_belongs_to_form_entry(): void
    {
        $form         = Form::factory()->create();
        $entry        = FormEntry::factory()->create(['form_id' => $form->id]);
        $notification = NotificationQueue::factory()->create(['form_entry_id' => $entry->id]);

        $relatedEntry = $notification->formEntry;

        $this->assertInstanceOf(FormEntry::class, $relatedEntry);
        $this->assertEquals($entry->id, $relatedEntry->id);
        $this->assertEquals($entry->name, $relatedEntry->name);
    }

    public function test_notification_queue_can_exist_without_form_entry(): void
    {
        $notification = NotificationQueue::factory()->create(['form_entry_id' => null]);

        $this->assertNull($notification->form_entry_id);
        $this->assertNull($notification->formEntry);
    }

    public function test_deleting_form_entry_sets_null_on_notifications(): void
    {
        $form         = Form::factory()->create();
        $entry        = FormEntry::factory()->create(['form_id' => $form->id]);
        $notification = NotificationQueue::factory()->create(['form_entry_id' => $entry->id]);

        $this->assertEquals($entry->id, $notification->form_entry_id);

        // Force delete (hard delete) to trigger the foreign key constraint
        // Note: Soft delete (delete()) won't trigger onDelete('set null')
        $entry->forceDelete();

        // Refresh notification from database
        $notification->refresh();

        // form_entry_id should be set to null due to onDelete('set null') in migration
        $this->assertNull($notification->form_entry_id);
    }

    public function test_form_entry_relationship_is_eager_loadable(): void
    {
        $form  = Form::factory()->create();
        $entry = FormEntry::factory()->create(['form_id' => $form->id]);

        NotificationQueue::factory()->count(3)->create(['form_entry_id' => $entry->id]);

        $entryWithNotifications = FormEntry::with('notifications')->find($entry->id);

        $this->assertTrue($entryWithNotifications->relationLoaded('notifications'));
        $this->assertCount(3, $entryWithNotifications->notifications);
    }

    public function test_notification_queue_form_entry_relationship_is_eager_loadable(): void
    {
        $form  = Form::factory()->create();
        $entry = FormEntry::factory()->create(['form_id' => $form->id]);

        $notification = NotificationQueue::factory()->create(['form_entry_id' => $entry->id]);

        $notificationWithEntry = NotificationQueue::with('formEntry')->find($notification->id);

        $this->assertTrue($notificationWithEntry->relationLoaded('formEntry'));
        $this->assertInstanceOf(FormEntry::class, $notificationWithEntry->formEntry);
        $this->assertEquals($entry->id, $notificationWithEntry->formEntry->id);
    }

    public function test_multiple_notifications_can_reference_same_form_entry(): void
    {
        $form  = Form::factory()->create();
        $entry = FormEntry::factory()->create(['form_id' => $form->id]);

        // Create multiple notifications for same entry (e.g., different rules or resync)
        $notification1 = NotificationQueue::factory()->create([
            'form_entry_id'   => $entry->id,
            'recipient_email' => 'admin@example.com',
        ]);

        $notification2 = NotificationQueue::factory()->create([
            'form_entry_id'   => $entry->id,
            'recipient_email' => 'manager@example.com',
        ]);

        $this->assertEquals($entry->id, $notification1->form_entry_id);
        $this->assertEquals($entry->id, $notification2->form_entry_id);

        $entryNotifications = $entry->notifications;

        $this->assertCount(2, $entryNotifications);
        $this->assertTrue($entryNotifications->contains($notification1));
        $this->assertTrue($entryNotifications->contains($notification2));
    }

    public function test_form_entry_id_is_in_notification_queue_fillable(): void
    {
        $form     = Form::factory()->create();
        $entry    = FormEntry::factory()->create(['form_id' => $form->id]);
        $template = \App\Models\NotificationTemplate::factory()->create();

        $notification = NotificationQueue::create([
            'template_id'     => $template->id,
            'form_entry_id'   => $entry->id,
            'recipient_email' => 'test@example.com',
            'subject'         => 'Test',
            'body_html'       => '<p>Test</p>',
            'body_text'       => 'Test',
            'trigger_data'    => [],
            'scheduled_at'    => now(),
            'status'          => 'pending',
        ]);

        $this->assertEquals($entry->id, $notification->form_entry_id);
    }

    public function test_can_query_notifications_by_form_entry_id(): void
    {
        $form   = Form::factory()->create();
        $entry1 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form->id]);

        NotificationQueue::factory()->count(3)->create(['form_entry_id' => $entry1->id]);
        NotificationQueue::factory()->count(2)->create(['form_entry_id' => $entry2->id]);

        $entry1Notifications = NotificationQueue::where('form_entry_id', $entry1->id)->get();
        $entry2Notifications = NotificationQueue::where('form_entry_id', $entry2->id)->get();

        $this->assertCount(3, $entry1Notifications);
        $this->assertCount(2, $entry2Notifications);
    }

    public function test_can_query_form_entries_with_notification_count(): void
    {
        $form = Form::factory()->create();

        $entry1 = FormEntry::factory()->create(['form_id' => $form->id]);
        NotificationQueue::factory()->count(5)->create(['form_entry_id' => $entry1->id]);

        $entry2 = FormEntry::factory()->create(['form_id' => $form->id]);
        NotificationQueue::factory()->count(2)->create(['form_entry_id' => $entry2->id]);

        $entry3 = FormEntry::factory()->create(['form_id' => $form->id]);
        // No notifications for entry3

        $entriesWithCounts = FormEntry::withCount('notifications')->get();

        $e1 = $entriesWithCounts->firstWhere('id', $entry1->id);
        $e2 = $entriesWithCounts->firstWhere('id', $entry2->id);
        $e3 = $entriesWithCounts->firstWhere('id', $entry3->id);

        $this->assertEquals(5, $e1->notifications_count);
        $this->assertEquals(2, $e2->notifications_count);
        $this->assertEquals(0, $e3->notifications_count);
    }
}
