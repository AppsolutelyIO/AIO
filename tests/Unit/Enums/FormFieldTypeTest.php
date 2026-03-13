<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Enums;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Tests\TestCase;

final class FormFieldTypeTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $cases = FormFieldType::cases();

        $this->assertCount(13, $cases);
        $this->assertSame('text', FormFieldType::Text->value);
        $this->assertSame('textarea', FormFieldType::Textarea->value);
        $this->assertSame('email', FormFieldType::Email->value);
        $this->assertSame('number', FormFieldType::Number->value);
        $this->assertSame('select', FormFieldType::Select->value);
        $this->assertSame('multiple_select', FormFieldType::MultipleSelect->value);
        $this->assertSame('radio', FormFieldType::Radio->value);
        $this->assertSame('checkbox', FormFieldType::Checkbox->value);
        $this->assertSame('file', FormFieldType::File->value);
        $this->assertSame('date', FormFieldType::Date->value);
        $this->assertSame('time', FormFieldType::Time->value);
        $this->assertSame('datetime', FormFieldType::DateTime->value);
        $this->assertSame('hidden', FormFieldType::Hidden->value);
    }

    public function test_label_returns_human_readable_string(): void
    {
        $this->assertSame('Text Input', FormFieldType::Text->label());
        $this->assertSame('Textarea', FormFieldType::Textarea->label());
        $this->assertSame('Email', FormFieldType::Email->label());
        $this->assertSame('Select Dropdown', FormFieldType::Select->label());
        $this->assertSame('Multiple Select', FormFieldType::MultipleSelect->label());
        $this->assertSame('Radio Buttons', FormFieldType::Radio->label());
        $this->assertSame('Checkboxes', FormFieldType::Checkbox->label());
        $this->assertSame('File Upload', FormFieldType::File->label());
        $this->assertSame('Hidden Field', FormFieldType::Hidden->label());
    }

    public function test_to_array_returns_all_cases_with_labels(): void
    {
        $array = FormFieldType::toArray();

        $this->assertCount(13, $array);
        $this->assertArrayHasKey('text', $array);
        $this->assertSame('Text Input', $array['text']);
        $this->assertArrayHasKey('select', $array);
        $this->assertSame('Select Dropdown', $array['select']);
    }

    // --- supportsOptions ---

    public function test_select_supports_options(): void
    {
        $this->assertTrue(FormFieldType::Select->supportsOptions());
    }

    public function test_multiple_select_supports_options(): void
    {
        $this->assertTrue(FormFieldType::MultipleSelect->supportsOptions());
    }

    public function test_radio_supports_options(): void
    {
        $this->assertTrue(FormFieldType::Radio->supportsOptions());
    }

    public function test_checkbox_supports_options(): void
    {
        $this->assertTrue(FormFieldType::Checkbox->supportsOptions());
    }

    public function test_text_does_not_support_options(): void
    {
        $this->assertFalse(FormFieldType::Text->supportsOptions());
    }

    public function test_email_does_not_support_options(): void
    {
        $this->assertFalse(FormFieldType::Email->supportsOptions());
    }

    public function test_number_does_not_support_options(): void
    {
        $this->assertFalse(FormFieldType::Number->supportsOptions());
    }

    public function test_file_does_not_support_options(): void
    {
        $this->assertFalse(FormFieldType::File->supportsOptions());
    }

    public function test_hidden_does_not_support_options(): void
    {
        $this->assertFalse(FormFieldType::Hidden->supportsOptions());
    }

    // --- supportsMultipleValues ---

    public function test_multiple_select_supports_multiple_values(): void
    {
        $this->assertTrue(FormFieldType::MultipleSelect->supportsMultipleValues());
    }

    public function test_checkbox_supports_multiple_values(): void
    {
        $this->assertTrue(FormFieldType::Checkbox->supportsMultipleValues());
    }

    public function test_select_does_not_support_multiple_values(): void
    {
        $this->assertFalse(FormFieldType::Select->supportsMultipleValues());
    }

    public function test_radio_does_not_support_multiple_values(): void
    {
        $this->assertFalse(FormFieldType::Radio->supportsMultipleValues());
    }

    public function test_text_does_not_support_multiple_values(): void
    {
        $this->assertFalse(FormFieldType::Text->supportsMultipleValues());
    }
}
