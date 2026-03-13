<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\FormEntrySpamStatus;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class FormTest extends TestCase
{
    use RefreshDatabase;

    // --- getEntriesCountAttribute ---

    public function test_entries_count_returns_zero_when_no_entries(): void
    {
        $form = Form::factory()->create();

        $this->assertSame(0, $form->entries_count);
    }

    public function test_entries_count_returns_total_entries(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->count(3)->create(['form_id' => $form->id]);

        $this->assertSame(3, $form->entries_count);
    }

    public function test_entries_count_includes_spam_entries(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->notSpam()->create(['form_id' => $form->id]);
        FormEntry::factory()->spam()->create(['form_id' => $form->id]);

        $this->assertSame(2, $form->entries_count);
    }

    // --- getValidEntriesCountAttribute ---

    public function test_valid_entries_count_excludes_spam(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->notSpam()->count(2)->create(['form_id' => $form->id]);
        FormEntry::factory()->spam()->create(['form_id' => $form->id]);

        $this->assertSame(2, $form->valid_entries_count);
    }

    public function test_valid_entries_count_returns_zero_when_all_spam(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->spam()->count(2)->create(['form_id' => $form->id]);

        $this->assertSame(0, $form->valid_entries_count);
    }

    // --- validEntries ---

    public function test_valid_entries_filters_by_valid_spam_status(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->notSpam()->create(['form_id' => $form->id]);
        FormEntry::factory()->spam()->create(['form_id' => $form->id]);

        $result = $form->validEntries()->get();

        $this->assertCount(1, $result);
        $this->assertSame(FormEntrySpamStatus::Valid->value, $result->first()->getRawOriginal('is_spam'));
    }
}
