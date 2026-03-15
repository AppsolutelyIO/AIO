<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\FormEntrySpamStatus;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class FormEntryTest extends TestCase
{
    use RefreshDatabase;

    // --- getFieldValue ---

    public function test_get_field_value_returns_existing_field(): void
    {
        $entry = FormEntry::factory()->withData([
            'email'   => 'john@example.com',
            'message' => 'Hello world',
        ])->create();

        $this->assertSame('john@example.com', $entry->getFieldValue('email'));
        $this->assertSame('Hello world', $entry->getFieldValue('message'));
    }

    public function test_get_field_value_returns_null_for_missing_field(): void
    {
        $entry = FormEntry::factory()->withData([
            'email' => 'john@example.com',
        ])->create();

        $this->assertNull($entry->getFieldValue('nonexistent'));
    }

    // --- setFieldValue ---

    public function test_set_field_value_sets_new_field(): void
    {
        $entry = FormEntry::factory()->withData([])->create();

        $entry->setFieldValue('color', 'blue');

        $this->assertSame('blue', $entry->getFieldValue('color'));
    }

    public function test_set_field_value_overwrites_existing_field(): void
    {
        $entry = FormEntry::factory()->withData([
            'color' => 'red',
        ])->create();

        $entry->setFieldValue('color', 'green');

        $this->assertSame('green', $entry->getFieldValue('color'));
    }

    // --- getMetaValue ---

    public function test_get_meta_value_returns_existing_key(): void
    {
        $entry = FormEntry::factory()->create([
            'meta' => ['source' => 'google', 'campaign' => 'summer'],
        ]);

        $this->assertSame('google', $entry->getMetaValue('source'));
    }

    public function test_get_meta_value_returns_null_for_missing_key(): void
    {
        $entry = FormEntry::factory()->create([
            'meta' => ['source' => 'google'],
        ]);

        $this->assertNull($entry->getMetaValue('nonexistent'));
    }

    public function test_get_meta_value_casts_integer_to_string(): void
    {
        $entry = FormEntry::factory()->create([
            'meta' => ['visits' => 42],
        ]);

        $result = $entry->getMetaValue('visits');

        $this->assertSame('42', $result);
        $this->assertIsString($result);
    }

    // --- markAsSpam ---

    public function test_mark_as_spam_updates_entry_to_spam(): void
    {
        $entry = FormEntry::factory()->notSpam()->create();

        $entry->markAsSpam();
        $entry->refresh();

        $this->assertSame(FormEntrySpamStatus::Spam, $entry->is_spam);
    }

    // --- markAsNotSpam ---

    public function test_mark_as_not_spam_updates_entry_to_valid(): void
    {
        $entry = FormEntry::factory()->spam()->create();

        $entry->markAsNotSpam();
        $entry->refresh();

        $this->assertSame(FormEntrySpamStatus::Valid, $entry->is_spam);
    }

    // --- getIsValidAttribute ---

    public function test_is_valid_returns_true_for_valid_entry(): void
    {
        $entry = FormEntry::factory()->notSpam()->create();

        $this->assertTrue($entry->is_valid);
    }

    public function test_is_valid_returns_false_for_spam_entry(): void
    {
        $entry = FormEntry::factory()->spam()->create();

        $this->assertFalse($entry->is_valid);
    }

    // --- getUserName ---

    public function test_get_user_name_returns_name_when_set(): void
    {
        $entry = FormEntry::factory()->create([
            'name'       => 'Jane Doe',
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
        ]);

        $this->assertSame('Jane Doe', $entry->getUserName());
    }

    public function test_get_user_name_returns_first_and_last_name_when_name_is_empty(): void
    {
        $entry = FormEntry::factory()->create([
            'name'       => '',
            'first_name' => 'John',
            'last_name'  => 'Smith',
        ]);

        $this->assertSame('John Smith', $entry->getUserName());
    }

    public function test_get_user_name_returns_first_and_last_name_when_name_is_null(): void
    {
        $entry = FormEntry::factory()->create([
            'name'       => null,
            'first_name' => 'Alice',
            'last_name'  => 'Johnson',
        ]);

        $this->assertSame('Alice Johnson', $entry->getUserName());
    }

    public function test_get_user_name_trims_whitespace(): void
    {
        $entry = FormEntry::factory()->create([
            'name'       => null,
            'first_name' => 'Bob',
            'last_name'  => null,
        ]);

        $this->assertSame('Bob', $entry->getUserName());
    }

    // --- getFormattedDataAttribute ---

    public function test_formatted_data_returns_empty_array_when_form_is_null(): void
    {
        $entry = FormEntry::factory()->create();

        // Simulate orphaned entry by nullifying the form relationship
        $entry->setRelation('form', null);

        $this->assertIsArray($entry->formatted_data);
        $this->assertEmpty($entry->formatted_data);
    }

    // --- getFullNameAttribute ---

    public function test_full_name_attribute_delegates_to_get_user_name(): void
    {
        $entry = FormEntry::factory()->create([
            'name' => 'Full Name Test',
        ]);

        $this->assertSame($entry->getUserName(), $entry->full_name);
    }
}
