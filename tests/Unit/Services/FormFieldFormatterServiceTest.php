<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Services\FormFieldFormatterService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class FormFieldFormatterServiceTest extends TestCase
{
    use RefreshDatabase;

    private FormFieldFormatterService $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new FormFieldFormatterService();
    }

    public function test_prepare_notification_data_returns_complete_array(): void
    {
        $form = Form::factory()->create(['name' => 'Contact Form']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Full Name',
            'type'    => 'text',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'data'    => ['name' => 'John Doe'],
        ]);

        $result = $this->formatter->prepareNotificationData($form, $entry);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('entry_id', $result);
        $this->assertArrayHasKey('form_id', $result);
        $this->assertArrayHasKey('form_name', $result);
        $this->assertArrayHasKey('user_name', $result);
        $this->assertArrayHasKey('form_fields_html', $result);
        $this->assertArrayHasKey('form_fields_text', $result);

        $this->assertEquals($entry->id, $result['entry_id']);
        $this->assertEquals($form->id, $result['form_id']);
        $this->assertEquals('Contact Form', $result['form_name']);
        $this->assertEquals('John Doe', $result['user_name']);
    }

    public function test_format_fields_as_html_creates_table_structure(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Full Name',
            'type'    => 'text',
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'email',
            'label'   => 'Email Address',
            'type'    => 'email',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'John Doe',
            'email'   => 'john@example.com',
            'data'    => ['name' => 'John Doe', 'email' => 'john@example.com'],
        ]);

        $result = $this->formatter->formatFieldsAsHtml($form, $entry);

        $this->assertStringContainsString('<table id="form-fields-table">', $result);
        $this->assertStringContainsString('Full Name', $result);
        $this->assertStringContainsString('John Doe', $result);
        $this->assertStringContainsString('Email Address', $result);
        $this->assertStringContainsString('john@example.com', $result);
        $this->assertStringContainsString('</table>', $result);
    }

    public function test_format_fields_as_text_creates_plain_text(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Full Name',
            'type'    => 'text',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'John Doe',
            'data'    => ['name' => 'John Doe'],
        ]);

        $result = $this->formatter->formatFieldsAsText($form, $entry);

        $this->assertStringContainsString('Full Name: John Doe', $result);
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringNotContainsString('>', $result);
    }

    public function test_format_value_handles_boolean_values(): void
    {
        $result = $this->formatter->formatValue(true, 'checkbox');

        $this->assertStringContainsString('✓ Yes', $result);
        $this->assertStringContainsString('#27ae60', $result);

        $result = $this->formatter->formatValue(false, 'checkbox');

        $this->assertStringContainsString('✗ No', $result);
        $this->assertStringContainsString('#e74c3c', $result);
    }

    public function test_format_value_handles_null_values(): void
    {
        $result = $this->formatter->formatValue(null, 'text');

        $this->assertStringContainsString('—', $result);
        $this->assertStringContainsString('#999', $result);
    }

    public function test_format_value_handles_url_values(): void
    {
        $result = $this->formatter->formatValue('https://example.com', 'text');

        $this->assertStringContainsString('<a href="https://example.com"', $result);
        $this->assertStringContainsString('#3498db', $result);
    }

    public function test_format_value_handles_file_arrays(): void
    {
        $files = [
            ['name' => 'document1.pdf'],
            ['name' => 'document2.pdf'],
        ];

        $result = $this->formatter->formatValue($files, 'file');

        $this->assertStringContainsString('document1.pdf', $result);
        $this->assertStringContainsString('document2.pdf', $result);
        $this->assertStringContainsString(', ', $result);
    }

    public function test_format_value_handles_checkbox_arrays(): void
    {
        $values = ['Option A', 'Option B', 'Option C'];

        $result = $this->formatter->formatValue($values, 'checkbox');

        $this->assertStringContainsString('Option A, Option B, Option C', $result);
    }

    public function test_format_value_escapes_html(): void
    {
        $result = $this->formatter->formatValue('<script>alert("xss")</script>', 'text');

        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function test_format_value_text_handles_boolean_values(): void
    {
        $result = $this->formatter->formatValueText(true, 'checkbox');
        $this->assertEquals('Yes', $result);

        $result = $this->formatter->formatValueText(false, 'checkbox');
        $this->assertEquals('No', $result);
    }

    public function test_format_value_text_handles_null_values(): void
    {
        $result = $this->formatter->formatValueText(null, 'text');

        $this->assertEquals('—', $result);
    }

    public function test_format_value_text_handles_file_arrays(): void
    {
        $files = [
            ['name' => 'doc1.pdf'],
            ['name' => 'doc2.pdf'],
        ];

        $result = $this->formatter->formatValueText($files, 'file');

        $this->assertEquals('doc1.pdf, doc2.pdf', $result);
    }

    public function test_format_value_text_handles_multiple_select_arrays(): void
    {
        $values = ['Red', 'Blue', 'Green'];

        $result = $this->formatter->formatValueText($values, 'multiple_select');

        $this->assertEquals('Red, Blue, Green', $result);
    }

    public function test_get_field_value_prefers_direct_properties(): void
    {
        $entry = FormEntry::factory()->create([
            'form_id' => Form::factory()->create()->id,
            'name'    => 'Direct Name',
            'email'   => 'direct@example.com',
            'data'    => ['name' => 'JSON Name', 'email' => 'json@example.com'],
        ]);

        $result = $this->formatter->getFieldValue($entry, 'name');
        $this->assertEquals('Direct Name', $result);

        $result = $this->formatter->getFieldValue($entry, 'email');
        $this->assertEquals('direct@example.com', $result);
    }

    public function test_get_field_value_falls_back_to_data_json(): void
    {
        $entry = FormEntry::factory()->create([
            'form_id' => Form::factory()->create()->id,
            'data'    => ['custom_field' => 'Custom Value'],
        ]);

        $result = $this->formatter->getFieldValue($entry, 'custom_field');

        $this->assertEquals('Custom Value', $result);
    }

    public function test_format_fields_skips_empty_values(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'name',
            'label'   => 'Full Name',
            'type'    => 'text',
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'empty_field',
            'label'   => 'Empty Field',
            'type'    => 'text',
        ]);

        $entry = FormEntry::factory()->create([
            'form_id' => $form->id,
            'name'    => 'John Doe',
            'data'    => ['name' => 'John Doe', 'empty_field' => ''],
        ]);

        $htmlResult = $this->formatter->formatFieldsAsHtml($form, $entry);
        $textResult = $this->formatter->formatFieldsAsText($form, $entry);

        $this->assertStringContainsString('Full Name', $htmlResult);
        $this->assertStringNotContainsString('Empty Field', $htmlResult);

        $this->assertStringContainsString('Full Name', $textResult);
        $this->assertStringNotContainsString('Empty Field', $textResult);
    }
}
