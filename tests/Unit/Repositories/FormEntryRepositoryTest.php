<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Repositories;

use Appsolutely\AIO\Enums\FormEntrySpamStatus;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\NotificationQueue;
use App\Models\User;
use Appsolutely\AIO\Repositories\FormEntryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class FormEntryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private FormEntryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(FormEntryRepository::class);
    }

    public function test_get_entries_without_notifications_returns_correct_entries(): void
    {
        $form = Form::factory()->create();

        // Entry with notifications
        $entryWithNotification = FormEntry::factory()->create(['form_id' => $form->id]);
        NotificationQueue::factory()->create(['form_entry_id' => $entryWithNotification->id]);

        // Entry without notifications
        $entryWithoutNotification = FormEntry::factory()->create(['form_id' => $form->id]);

        $result = $this->repository->getEntriesWithoutNotifications($form->id);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $entryWithoutNotification->id));
        $this->assertFalse($result->contains('id', $entryWithNotification->id));
    }

    public function test_get_entries_without_notifications_respects_limit(): void
    {
        $form = Form::factory()->create();

        // Create 5 entries without notifications
        FormEntry::factory()->count(5)->create(['form_id' => $form->id]);

        $result = $this->repository->getEntriesWithoutNotifications($form->id, 3);

        $this->assertCount(3, $result);
    }

    public function test_get_entries_without_notifications_returns_all_forms_when_no_form_id(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->create(['form_id' => $form1->id]);
        FormEntry::factory()->create(['form_id' => $form2->id]);

        $result = $this->repository->getEntriesWithoutNotifications();

        $this->assertCount(2, $result);
    }

    public function test_has_notifications_returns_true_when_notifications_exist(): void
    {
        $entry = FormEntry::factory()->create();
        NotificationQueue::factory()->create(['form_entry_id' => $entry->id]);

        $result = $this->repository->hasNotifications($entry->id);

        $this->assertTrue($result);
    }

    public function test_has_notifications_returns_false_when_no_notifications_exist(): void
    {
        $entry = FormEntry::factory()->create();

        $result = $this->repository->hasNotifications($entry->id);

        $this->assertFalse($result);
    }

    public function test_get_entries_with_notifications_count_returns_correct_counts(): void
    {
        $form = Form::factory()->create();

        $entry1 = FormEntry::factory()->create(['form_id' => $form->id, 'name' => 'Entry 1']);
        NotificationQueue::factory()->count(3)->create(['form_entry_id' => $entry1->id]);

        $entry2 = FormEntry::factory()->create(['form_id' => $form->id, 'name' => 'Entry 2']);
        NotificationQueue::factory()->count(2)->create(['form_entry_id' => $entry2->id]);

        $result = $this->repository->getEntriesWithNotificationsCount($form->id);

        $this->assertCount(2, $result);

        $firstEntry = $result->firstWhere('id', $entry1->id);
        $this->assertEquals(3, $firstEntry->notifications_count);

        $secondEntry = $result->firstWhere('id', $entry2->id);
        $this->assertEquals(2, $secondEntry->notifications_count);
    }

    public function test_get_by_ids_returns_correct_entries(): void
    {
        $form = Form::factory()->create();

        $entry1 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry3 = FormEntry::factory()->create(['form_id' => $form->id]);

        $result = $this->repository->getByIds([$entry1->id, $entry3->id]);

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $entry1->id));
        $this->assertTrue($result->contains('id', $entry3->id));
        $this->assertFalse($result->contains('id', $entry2->id));
    }

    public function test_get_by_ids_returns_empty_collection_for_empty_array(): void
    {
        $result = $this->repository->getByIds([]);

        $this->assertCount(0, $result);
    }

    public function test_get_entries_by_filters_paginated_filters_by_form_id(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->create(['form_id' => $form1->id]);
        FormEntry::factory()->create(['form_id' => $form1->id]);
        FormEntry::factory()->create(['form_id' => $form2->id]);

        $paginator = $this->repository->getEntriesByFiltersPaginated(['form_id' => $form1->id, 'per_page' => 100]);
        $result    = collect($paginator->items());

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn ($entry) => $entry->form_id === $form1->id));
    }

    public function test_get_entries_by_filters_paginated_filters_by_form_slug(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-form']);

        FormEntry::factory()->count(2)->create(['form_id' => $form->id]);

        $paginator = $this->repository->getEntriesByFiltersPaginated(['form_slug' => 'contact-form', 'per_page' => 100]);
        $result    = collect($paginator->items());

        $this->assertCount(2, $result);
    }

    public function test_get_entries_by_filters_paginated_filters_by_entry_id_range(): void
    {
        $form = Form::factory()->create();

        $entry1 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry3 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry4 = FormEntry::factory()->create(['form_id' => $form->id]);

        $paginator = $this->repository->getEntriesByFiltersPaginated([
            'form_id'       => $form->id,
            'entry_id_from' => (string) $entry2->id,
            'per_page'      => 100,
        ]);
        $result = collect($paginator->items());
        $this->assertGreaterThanOrEqual(3, $result->count());
        $this->assertFalse($result->contains('id', $entry1->id));

        $paginator = $this->repository->getEntriesByFiltersPaginated([
            'form_id'     => $form->id,
            'entry_id_to' => (string) $entry3->id,
            'per_page'    => 100,
        ]);
        $result = collect($paginator->items());
        $this->assertLessThanOrEqual(3, $result->count());
        $this->assertFalse($result->contains('id', $entry4->id));

        $paginator = $this->repository->getEntriesByFiltersPaginated([
            'form_id'       => $form->id,
            'entry_id_from' => (string) $entry2->id,
            'entry_id_to'   => (string) $entry3->id,
            'per_page'      => 100,
        ]);
        $result = collect($paginator->items());
        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $entry2->id));
        $this->assertTrue($result->contains('id', $entry3->id));
    }

    public function test_get_entries_by_filters_paginated_filters_by_date_range(): void
    {
        $form = Form::factory()->create();

        $oldEntry    = FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now()->subDays(10),
        ]);
        $recentEntry = FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now()->subDays(2),
        ]);

        $paginator = $this->repository->getEntriesByFiltersPaginated([
            'form_id'   => $form->id,
            'from_date' => now()->subDays(5)->format('Y-m-d'),
            'per_page'  => 100,
        ]);
        $result = collect($paginator->items());
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $recentEntry->id));
        $this->assertFalse($result->contains('id', $oldEntry->id));
    }

    public function test_get_entries_by_filters_paginated_filters_by_trigger_reference(): void
    {
        $form1 = Form::factory()->create(['slug' => 'contact-form']);
        $form2 = Form::factory()->create(['slug' => 'newsletter-form']);

        FormEntry::factory()->create(['form_id' => $form1->id]);
        FormEntry::factory()->create(['form_id' => $form2->id]);

        $paginator = $this->repository->getEntriesByFiltersPaginated([
            'trigger_reference' => 'contact-form',
            'per_page'          => 100,
        ]);
        $result = collect($paginator->items());
        $this->assertCount(1, $result);
        $this->assertEquals($form1->id, $result->first()->form_id);
    }

    public function test_get_entries_by_filters_paginated_handles_wildcard_trigger_reference(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->create(['form_id' => $form1->id]);
        FormEntry::factory()->create(['form_id' => $form2->id]);

        $paginator = $this->repository->getEntriesByFiltersPaginated([
            'trigger_reference' => '*',
            'per_page'          => 100,
        ]);
        $result = collect($paginator->items());
        $this->assertCount(2, $result);
    }

    public function test_get_form_stats_returns_correct_counts(): void
    {
        $form = Form::factory()->create();

        // Create valid entries (submitted today)
        FormEntry::factory()->count(3)->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        // Create spam entries
        FormEntry::factory()->spam()->count(2)->create([
            'form_id'      => $form->id,
            'submitted_at' => now(),
        ]);

        // Create an old valid entry (outside this week)
        FormEntry::factory()->create([
            'form_id'      => $form->id,
            'submitted_at' => now()->subMonth(),
        ]);

        $stats = $this->repository->getFormStats($form->id);

        $this->assertEquals(6, $stats['total']);
        $this->assertEquals(4, $stats['valid']);
        $this->assertEquals(2, $stats['spam']);
        $this->assertEquals(3, $stats['today']);
        $this->assertGreaterThanOrEqual(3, $stats['this_week']);
    }

    public function test_get_form_stats_returns_zeros_for_empty_form(): void
    {
        $form = Form::factory()->create();

        $stats = $this->repository->getFormStats($form->id);

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['valid']);
        $this->assertEquals(0, $stats['spam']);
        $this->assertEquals(0, $stats['today']);
        $this->assertEquals(0, $stats['this_week']);
    }

    public function test_get_entries_by_filters_paginated_combines_multiple_filters(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-form']);

        $entry1 = FormEntry::factory()->create([
            'form_id'    => $form->id,
            'created_at' => now()->subDays(5),
        ]);
        $entry2 = FormEntry::factory()->create([
            'form_id'    => $form->id,
            'created_at' => now()->subDays(2),
        ]);
        $entry3 = FormEntry::factory()->create([
            'form_id'    => $form->id,
            'created_at' => now()->subDays(1),
        ]);

        $paginator = $this->repository->getEntriesByFiltersPaginated([
            'form_slug'     => 'contact-form',
            'from_date'     => now()->subDays(3)->format('Y-m-d'),
            'entry_id_from' => (string) $entry2->id,
            'entry_id_to'   => (string) $entry2->id,
            'per_page'      => 100,
        ]);
        $result = collect($paginator->items());
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains('id', $entry2->id));
    }

    // ── getEntriesByForm ─────────────────────────────────────────────

    public function test_get_entries_by_form_returns_entries_for_form(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->count(3)->create(['form_id' => $form1->id]);
        FormEntry::factory()->count(2)->create(['form_id' => $form2->id]);

        $result = $this->repository->getEntriesByForm($form1->id);

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->form_id === $form1->id));
    }

    public function test_get_entries_by_form_excludes_spam_by_default(): void
    {
        $form = Form::factory()->create();

        FormEntry::factory()->count(2)->create(['form_id' => $form->id]);
        FormEntry::factory()->spam()->count(3)->create(['form_id' => $form->id]);

        $result = $this->repository->getEntriesByForm($form->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->is_spam === FormEntrySpamStatus::Valid));
    }

    public function test_get_entries_by_form_includes_spam_when_requested(): void
    {
        $form = Form::factory()->create();

        FormEntry::factory()->count(2)->create(['form_id' => $form->id]);
        FormEntry::factory()->spam()->count(3)->create(['form_id' => $form->id]);

        $result = $this->repository->getEntriesByForm($form->id, true);

        $this->assertCount(5, $result);
    }

    public function test_get_entries_by_form_returns_empty_collection_for_no_entries(): void
    {
        $form = Form::factory()->create();

        $result = $this->repository->getEntriesByForm($form->id);

        $this->assertCount(0, $result);
    }

    public function test_get_entries_by_form_orders_by_submitted_at_desc(): void
    {
        $form = Form::factory()->create();

        $older = FormEntry::factory()->create(['form_id' => $form->id, 'submitted_at' => now()->subDays(2)]);
        $newer = FormEntry::factory()->create(['form_id' => $form->id, 'submitted_at' => now()]);

        $result = $this->repository->getEntriesByForm($form->id);

        $this->assertEquals($newer->id, $result->first()->id);
        $this->assertEquals($older->id, $result->last()->id);
    }

    // ── getPaginatedEntriesByForm ────────────────────────────────────

    public function test_get_paginated_entries_by_form_returns_paginated_results(): void
    {
        $form = Form::factory()->create();

        FormEntry::factory()->count(20)->create(['form_id' => $form->id]);

        $result = $this->repository->getPaginatedEntriesByForm($form->id, 5);

        $this->assertCount(5, $result->items());
        $this->assertEquals(20, $result->total());
    }

    public function test_get_paginated_entries_by_form_includes_spam_by_default(): void
    {
        $form = Form::factory()->create();

        FormEntry::factory()->count(2)->create(['form_id' => $form->id]);
        FormEntry::factory()->spam()->count(3)->create(['form_id' => $form->id]);

        $result = $this->repository->getPaginatedEntriesByForm($form->id, 100);

        $this->assertEquals(5, $result->total());
    }

    public function test_get_paginated_entries_by_form_excludes_spam_when_requested(): void
    {
        $form = Form::factory()->create();

        FormEntry::factory()->count(2)->create(['form_id' => $form->id]);
        FormEntry::factory()->spam()->count(3)->create(['form_id' => $form->id]);

        $result = $this->repository->getPaginatedEntriesByForm($form->id, 100, false);

        $this->assertEquals(2, $result->total());
    }

    // ── getRecentEntries ─────────────────────────────────────────────

    public function test_get_recent_entries_returns_limited_results(): void
    {
        FormEntry::factory()->count(10)->create();

        $result = $this->repository->getRecentEntries(5);

        $this->assertCount(5, $result);
    }

    public function test_get_recent_entries_excludes_spam(): void
    {
        FormEntry::factory()->count(3)->create();
        FormEntry::factory()->spam()->count(2)->create();

        $result = $this->repository->getRecentEntries(10);

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->is_spam === FormEntrySpamStatus::Valid));
    }

    public function test_get_recent_entries_orders_by_submitted_at_desc(): void
    {
        $older = FormEntry::factory()->create(['submitted_at' => now()->subDays(5)]);
        $newer = FormEntry::factory()->create(['submitted_at' => now()]);

        $result = $this->repository->getRecentEntries(10);

        $this->assertEquals($newer->id, $result->first()->id);
        $this->assertEquals($older->id, $result->last()->id);
    }

    public function test_get_recent_entries_returns_entries_across_all_forms(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->create(['form_id' => $form1->id]);
        FormEntry::factory()->create(['form_id' => $form2->id]);

        $result = $this->repository->getRecentEntries(10);

        $this->assertCount(2, $result);
    }

    // ── getEntriesByUser ─────────────────────────────────────────────

    public function test_get_entries_by_user_returns_user_entries(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        FormEntry::factory()->count(3)->create(['user_id' => $user1->id]);
        FormEntry::factory()->count(2)->create(['user_id' => $user2->id]);

        $result = $this->repository->getEntriesByUser($user1->id);

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->user_id === $user1->id));
    }

    public function test_get_entries_by_user_returns_empty_collection_for_no_entries(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->getEntriesByUser($user->id);

        $this->assertCount(0, $result);
    }

    // ── getEntriesByEmail ────────────────────────────────────────────

    public function test_get_entries_by_email_returns_matching_entries(): void
    {
        FormEntry::factory()->count(2)->create(['email' => 'test@example.com']);
        FormEntry::factory()->create(['email' => 'other@example.com']);

        $result = $this->repository->getEntriesByEmail('test@example.com');

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->email === 'test@example.com'));
    }

    public function test_get_entries_by_email_returns_empty_collection_for_no_match(): void
    {
        FormEntry::factory()->create(['email' => 'existing@example.com']);

        $result = $this->repository->getEntriesByEmail('nonexistent@example.com');

        $this->assertCount(0, $result);
    }

    // ── markAsSpam ───────────────────────────────────────────────────

    public function test_mark_as_spam_updates_multiple_entries(): void
    {
        $entry1 = FormEntry::factory()->notSpam()->create();
        $entry2 = FormEntry::factory()->notSpam()->create();
        $entry3 = FormEntry::factory()->notSpam()->create();

        $count = $this->repository->markAsSpam([$entry1->id, $entry2->id]);

        $this->assertEquals(2, $count);
        $this->assertEquals(FormEntrySpamStatus::Spam, $entry1->fresh()->is_spam);
        $this->assertEquals(FormEntrySpamStatus::Spam, $entry2->fresh()->is_spam);
        $this->assertEquals(FormEntrySpamStatus::Valid, $entry3->fresh()->is_spam);
    }

    public function test_mark_as_spam_returns_zero_for_empty_array(): void
    {
        $count = $this->repository->markAsSpam([]);

        $this->assertEquals(0, $count);
    }

    // ── markAsNotSpam ────────────────────────────────────────────────

    public function test_mark_as_not_spam_updates_multiple_entries(): void
    {
        $entry1 = FormEntry::factory()->spam()->create();
        $entry2 = FormEntry::factory()->spam()->create();
        $entry3 = FormEntry::factory()->spam()->create();

        $count = $this->repository->markAsNotSpam([$entry1->id, $entry2->id]);

        $this->assertEquals(2, $count);
        $this->assertEquals(FormEntrySpamStatus::Valid, $entry1->fresh()->is_spam);
        $this->assertEquals(FormEntrySpamStatus::Valid, $entry2->fresh()->is_spam);
        $this->assertEquals(FormEntrySpamStatus::Spam, $entry3->fresh()->is_spam);
    }

    public function test_mark_as_not_spam_returns_zero_for_empty_array(): void
    {
        $count = $this->repository->markAsNotSpam([]);

        $this->assertEquals(0, $count);
    }

    // ── markSingleAsSpam ─────────────────────────────────────────────

    public function test_mark_single_as_spam_marks_entry(): void
    {
        $entry = FormEntry::factory()->notSpam()->create();

        $result = $this->repository->markSingleAsSpam($entry->id);

        $this->assertTrue($result);
        $this->assertEquals(FormEntrySpamStatus::Spam, $entry->fresh()->is_spam);
    }

    public function test_mark_single_as_spam_throws_for_nonexistent_entry(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->markSingleAsSpam(99999);
    }

    // ── markSingleAsNotSpam ──────────────────────────────────────────

    public function test_mark_single_as_not_spam_marks_entry(): void
    {
        $entry = FormEntry::factory()->spam()->create();

        $result = $this->repository->markSingleAsNotSpam($entry->id);

        $this->assertTrue($result);
        $this->assertEquals(FormEntrySpamStatus::Valid, $entry->fresh()->is_spam);
    }

    public function test_mark_single_as_not_spam_throws_for_nonexistent_entry(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repository->markSingleAsNotSpam(99999);
    }

    // ── getSpamEntries ───────────────────────────────────────────────

    public function test_get_spam_entries_returns_only_spam(): void
    {
        FormEntry::factory()->spam()->count(3)->create();
        FormEntry::factory()->notSpam()->count(2)->create();

        $result = $this->repository->getSpamEntries();

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->is_spam === FormEntrySpamStatus::Spam));
    }

    public function test_get_spam_entries_filters_by_form_id(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->spam()->count(2)->create(['form_id' => $form1->id]);
        FormEntry::factory()->spam()->count(3)->create(['form_id' => $form2->id]);

        $result = $this->repository->getSpamEntries($form1->id);

        $this->assertCount(2, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->form_id === $form1->id));
    }

    public function test_get_spam_entries_returns_all_spam_when_no_form_id(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->spam()->count(2)->create(['form_id' => $form1->id]);
        FormEntry::factory()->spam()->count(3)->create(['form_id' => $form2->id]);

        $result = $this->repository->getSpamEntries();

        $this->assertCount(5, $result);
    }

    public function test_get_spam_entries_returns_empty_collection_when_no_spam(): void
    {
        FormEntry::factory()->notSpam()->count(3)->create();

        $result = $this->repository->getSpamEntries();

        $this->assertCount(0, $result);
    }

    // ── createEntryWithSpamCheck ─────────────────────────────────────

    public function test_create_entry_with_spam_check_creates_valid_entry(): void
    {
        $form = Form::factory()->create();

        $entry = $this->repository->createEntryWithSpamCheck([
            'form_id'    => $form->id,
            'name'       => 'John Doe',
            'email'      => 'john@example.com',
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'data'       => ['message' => 'Hello there'],
        ]);

        $this->assertInstanceOf(FormEntry::class, $entry);
        $this->assertEquals(FormEntrySpamStatus::Valid, $entry->is_spam);
        $this->assertNotNull($entry->submitted_at);
    }

    public function test_create_entry_with_spam_check_detects_spam_keywords(): void
    {
        $form = Form::factory()->create();

        $entry = $this->repository->createEntryWithSpamCheck([
            'form_id'    => $form->id,
            'name'       => 'Win a free viagra',
            'email'      => 'spammer@example.com',
            'first_name' => 'Spam',
            'last_name'  => 'Bot',
            'data'       => ['message' => 'Buy now'],
        ]);

        $this->assertEquals(FormEntrySpamStatus::Spam, $entry->is_spam);
    }

    public function test_create_entry_with_spam_check_detects_spam_in_data_field(): void
    {
        $form = Form::factory()->create();

        $entry = $this->repository->createEntryWithSpamCheck([
            'form_id'    => $form->id,
            'name'       => 'Normal Name',
            'email'      => 'normal@example.com',
            'first_name' => 'Normal',
            'last_name'  => 'Person',
            'data'       => ['message' => 'Visit our casino today'],
        ]);

        $this->assertEquals(FormEntrySpamStatus::Spam, $entry->is_spam);
    }

    public function test_create_entry_with_spam_check_detects_invalid_email(): void
    {
        $form = Form::factory()->create();

        $entry = $this->repository->createEntryWithSpamCheck([
            'form_id'    => $form->id,
            'name'       => 'Normal Name',
            'email'      => 'not-a-valid-email',
            'first_name' => 'Normal',
            'last_name'  => 'Person',
            'data'       => ['message' => 'Hello'],
        ]);

        $this->assertEquals(FormEntrySpamStatus::Spam, $entry->is_spam);
    }

    public function test_create_entry_with_spam_check_persists_entry(): void
    {
        $form = Form::factory()->create();

        $entry = $this->repository->createEntryWithSpamCheck([
            'form_id'    => $form->id,
            'name'       => 'Jane Doe',
            'email'      => 'jane@example.com',
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
            'data'       => ['message' => 'Legitimate inquiry'],
        ]);

        $this->assertDatabaseHas('form_entries', ['id' => $entry->id]);
    }

    // ── getValidEntriesForExport ─────────────────────────────────────

    public function test_get_valid_entries_for_export_excludes_spam(): void
    {
        FormEntry::factory()->count(3)->create();
        FormEntry::factory()->spam()->count(2)->create();

        $result = $this->repository->getValidEntriesForExport();

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->is_spam === FormEntrySpamStatus::Valid));
    }

    public function test_get_valid_entries_for_export_filters_by_form_id(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->count(3)->create(['form_id' => $form1->id]);
        FormEntry::factory()->count(2)->create(['form_id' => $form2->id]);

        $result = $this->repository->getValidEntriesForExport($form1->id);

        $this->assertCount(3, $result);
        $this->assertTrue($result->every(fn (FormEntry $e) => $e->form_id === $form1->id));
    }

    public function test_get_valid_entries_for_export_returns_all_forms_when_no_form_id(): void
    {
        $form1 = Form::factory()->create();
        $form2 = Form::factory()->create();

        FormEntry::factory()->count(2)->create(['form_id' => $form1->id]);
        FormEntry::factory()->count(3)->create(['form_id' => $form2->id]);

        $result = $this->repository->getValidEntriesForExport();

        $this->assertCount(5, $result);
    }

    // ── countValid ───────────────────────────────────────────────────

    public function test_count_valid_returns_correct_count(): void
    {
        FormEntry::factory()->count(4)->create();
        FormEntry::factory()->spam()->count(2)->create();

        $result = $this->repository->countValid();

        $this->assertEquals(4, $result);
    }

    public function test_count_valid_returns_zero_when_no_valid_entries(): void
    {
        FormEntry::factory()->spam()->count(3)->create();

        $result = $this->repository->countValid();

        $this->assertEquals(0, $result);
    }

    // ── countSpam ────────────────────────────────────────────────────

    public function test_count_spam_returns_correct_count(): void
    {
        FormEntry::factory()->count(3)->create();
        FormEntry::factory()->spam()->count(5)->create();

        $result = $this->repository->countSpam();

        $this->assertEquals(5, $result);
    }

    public function test_count_spam_returns_zero_when_no_spam_entries(): void
    {
        FormEntry::factory()->count(3)->create();

        $result = $this->repository->countSpam();

        $this->assertEquals(0, $result);
    }

    // ── countValidByDateRange ────────────────────────────────────────

    public function test_count_valid_by_date_range_returns_correct_count(): void
    {
        FormEntry::factory()->create(['submitted_at' => now()->subDays(3)]);
        FormEntry::factory()->create(['submitted_at' => now()->subDays(1)]);
        FormEntry::factory()->create(['submitted_at' => now()->subDays(10)]);
        FormEntry::factory()->spam()->create(['submitted_at' => now()->subDays(2)]);

        $result = $this->repository->countValidByDateRange(
            now()->subDays(5)->startOfDay(),
            now()->endOfDay()
        );

        $this->assertEquals(2, $result);
    }

    public function test_count_valid_by_date_range_handles_same_start_and_end_date(): void
    {
        $date = now()->subDay();

        FormEntry::factory()->create(['submitted_at' => $date]);
        FormEntry::factory()->create(['submitted_at' => $date]);
        FormEntry::factory()->create(['submitted_at' => now()->subDays(5)]);

        $result = $this->repository->countValidByDateRange(
            $date->toDateString(),
            $date->toDateString()
        );

        $this->assertEquals(2, $result);
    }

    public function test_count_valid_by_date_range_excludes_spam(): void
    {
        $date = now();

        FormEntry::factory()->count(2)->create(['submitted_at' => $date]);
        FormEntry::factory()->spam()->count(3)->create(['submitted_at' => $date]);

        $result = $this->repository->countValidByDateRange(
            $date->startOfDay(),
            $date->endOfDay()
        );

        $this->assertEquals(2, $result);
    }

    public function test_count_valid_by_date_range_returns_zero_for_empty_range(): void
    {
        FormEntry::factory()->create(['submitted_at' => now()]);

        $result = $this->repository->countValidByDateRange(
            now()->subDays(10)->startOfDay(),
            now()->subDays(5)->endOfDay()
        );

        $this->assertEquals(0, $result);
    }
}
