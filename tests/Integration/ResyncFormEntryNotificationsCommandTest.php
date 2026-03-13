<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Integration;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Models\NotificationQueue;
use Appsolutely\AIO\Models\NotificationRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class ResyncFormEntryNotificationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_resyncs_entries_without_notifications(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Name',
            'type'    => 'text',
        ]);

        $rule = NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        // Entry without notification
        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'data'    => json_encode(['name' => 'John Doe']),
        ]);

        $this->artisan('notifications:resync-form-entries')
            ->expectsOutput('🔄 Starting form entry notification resync...')
            ->assertExitCode(0);

        // Check that notification was queued
        $this->assertDatabaseHas('notification_queue', [
            'rule_id'       => $rule->id,
            'form_entry_id' => $entry->id,
            'status'        => 'pending',
        ]);
    }

    public function test_command_with_dry_run_does_not_create_notifications(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-form']);
        FormField::factory()->create(['form_id' => $form->id]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        $entry = FormEntry::factory()->create(['form_id' => $form->id]);

        $this->artisan('notifications:resync-form-entries', ['--dry-run' => true])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('notification_queue', [
            'form_entry_id' => $entry->id,
        ]);
    }

    public function test_command_skips_entries_with_existing_notifications(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-form']);
        FormField::factory()->create(['form_id' => $form->id]);

        $rule = NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        $entry = FormEntry::factory()->create(['form_id' => $form->id]);

        // Create existing notification
        NotificationQueue::factory()->create([
            'rule_id'       => $rule->id,
            'form_entry_id' => $entry->id,
        ]);

        $initialCount = NotificationQueue::count();

        $this->artisan('notifications:resync-form-entries')
            ->assertExitCode(0);

        // Should not create duplicate
        $this->assertEquals($initialCount, NotificationQueue::count());
    }

    public function test_command_with_force_flag_creates_duplicate_notifications(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-form']);
        FormField::factory()->create(['form_id' => $form->id]);

        $rule = NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        $entry = FormEntry::factory()->create(['form_id' => $form->id]);

        // Create existing notification
        NotificationQueue::factory()->create([
            'rule_id'       => $rule->id,
            'form_entry_id' => $entry->id,
        ]);

        $initialCount = NotificationQueue::count();

        $this->artisan('notifications:resync-form-entries', ['--force' => true])
            ->assertExitCode(0);

        // Should create another notification
        $this->assertGreaterThan($initialCount, NotificationQueue::count());
    }

    public function test_command_filters_by_form_slug(): void
    {
        $form1 = Form::factory()->create(['slug' => 'contact-form']);
        $form2 = Form::factory()->create(['slug' => 'newsletter-form']);

        FormField::factory()->create(['form_id' => $form1->id]);
        FormField::factory()->create(['form_id' => $form2->id]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'newsletter-form',
            'status'            => Status::ACTIVE,
        ]);

        $entry1 = FormEntry::factory()->create(['form_id' => $form1->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form2->id]);

        $this->artisan('notifications:resync-form-entries', ['--form' => 'contact-form'])
            ->assertExitCode(0);

        // Should only queue for form1
        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry1->id,
        ]);

        $this->assertDatabaseMissing('notification_queue', [
            'form_entry_id' => $entry2->id,
        ]);
    }

    public function test_command_filters_by_form_id(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormField::factory()->create(['form_id' => $form1->id]);
        FormField::factory()->create(['form_id' => $form2->id]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => $form1->slug,
            'status'            => Status::ACTIVE,
        ]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => $form2->slug,
            'status'            => Status::ACTIVE,
        ]);

        $entry1 = FormEntry::factory()->create(['form_id' => $form1->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form2->id]);

        $this->artisan('notifications:resync-form-entries', ['--form-id' => $form1->id])
            ->assertExitCode(0);

        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry1->id,
        ]);

        $this->assertDatabaseMissing('notification_queue', [
            'form_entry_id' => $entry2->id,
        ]);
    }

    public function test_command_filters_by_single_entry_id(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => $form->slug,
            'status'            => Status::ACTIVE,
        ]);

        $entry1 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form->id]);

        $this->artisan('notifications:resync-form-entries', ['--entry-id' => $entry1->id])
            ->assertExitCode(0);

        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry1->id,
        ]);

        $this->assertDatabaseMissing('notification_queue', [
            'form_entry_id' => $entry2->id,
        ]);
    }

    public function test_command_filters_by_multiple_entry_ids(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => $form->slug,
            'status'            => Status::ACTIVE,
        ]);

        $entry1 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry3 = FormEntry::factory()->create(['form_id' => $form->id]);

        $this->artisan('notifications:resync-form-entries', [
            '--entry-ids' => "{$entry1->id},{$entry3->id}",
        ])
            ->assertExitCode(0);

        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry1->id,
        ]);

        $this->assertDatabaseMissing('notification_queue', [
            'form_entry_id' => $entry2->id,
        ]);

        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry3->id,
        ]);
    }

    public function test_command_filters_by_entry_id_range(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => $form->slug,
            'status'            => Status::ACTIVE,
        ]);

        $entry1 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry3 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry4 = FormEntry::factory()->create(['form_id' => $form->id]);

        $this->artisan('notifications:resync-form-entries', [
            '--entry-id-from' => $entry2->id,
            '--entry-id-to'   => $entry3->id,
        ])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('notification_queue', [
            'form_entry_id' => $entry1->id,
        ]);

        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry2->id,
        ]);

        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry3->id,
        ]);

        $this->assertDatabaseMissing('notification_queue', [
            'form_entry_id' => $entry4->id,
        ]);
    }

    public function test_command_filters_by_date_range(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id]);

        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => $form->slug,
            'status'            => Status::ACTIVE,
        ]);

        $oldEntry = FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now()->subDays(10),
        ]);

        $recentEntry = FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now()->subDays(2),
        ]);

        $this->artisan('notifications:resync-form-entries', [
            '--from-date' => now()->subDays(5)->format('Y-m-d'),
        ])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('notification_queue', [
            'form_entry_id' => $oldEntry->id,
        ]);

        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $recentEntry->id,
        ]);
    }

    public function test_command_returns_failure_when_no_active_rules(): void
    {
        // Create inactive rule
        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'test-form',
            'status'            => Status::INACTIVE,
        ]);

        $this->artisan('notifications:resync-form-entries')
            ->expectsOutput('No active form submission notification rules found.')
            ->assertExitCode(1);
    }

    public function test_command_handles_wildcard_trigger_reference(): void
    {
        $form1 = Form::factory()->create(['slug' => 'form-1']);
        $form2 = Form::factory()->create(['slug' => 'form-2']);

        FormField::factory()->create(['form_id' => $form1->id]);
        FormField::factory()->create(['form_id' => $form2->id]);

        // Wildcard rule
        NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => '*',
            'status'            => Status::ACTIVE,
        ]);

        $entry1 = FormEntry::factory()->create(['form_id' => $form1->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form2->id]);

        $this->artisan('notifications:resync-form-entries')
            ->assertExitCode(0);

        // Both should be queued
        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry1->id,
        ]);

        $this->assertDatabaseHas('notification_queue', [
            'form_entry_id' => $entry2->id,
        ]);
    }

    public function test_command_stores_form_entry_id_in_notification_queue(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-form']);
        FormField::factory()->create(['form_id' => $form->id]);

        $rule = NotificationRule::factory()->create([
            'trigger_type'      => 'form_submission',
            'trigger_reference' => 'contact-form',
            'status'            => Status::ACTIVE,
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'Test User',
        ]);

        $this->artisan('notifications:resync-form-entries')
            ->assertExitCode(0);

        $notification = NotificationQueue::where('form_entry_id', $entry->id)->first();

        $this->assertNotNull($notification);
        $this->assertEquals($entry->id, $notification->form_entry_id);
        $this->assertEquals($rule->id, $notification->rule_id);

        // Verify trigger_data includes entry_id and form_id
        $triggerData = $notification->trigger_data;
        $this->assertIsArray($triggerData);
        $this->assertEquals($entry->id, $triggerData['entry_id']);
        $this->assertEquals($form->id, $triggerData['form_id']);
    }
}
