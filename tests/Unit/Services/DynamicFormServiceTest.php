<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Services\DynamicFormService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class DynamicFormServiceTest extends TestCase
{
    use RefreshDatabase;

    private DynamicFormService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DynamicFormService::class);
    }

    // --- getFormBySlug ---

    public function test_get_form_by_slug_returns_form_when_found(): void
    {
        $form = Form::factory()->create(['slug' => 'contact-us']);

        $result = $this->service->getFormBySlug('contact-us');

        $this->assertInstanceOf(Form::class, $result);
        $this->assertEquals($form->id, $result->id);
    }

    public function test_get_form_by_slug_returns_null_when_not_found(): void
    {
        $result = $this->service->getFormBySlug('non-existent-form');

        $this->assertNull($result);
    }

    // --- createForm ---

    public function test_create_form_creates_form_with_provided_slug(): void
    {
        $formData = [
            'name' => 'My Form',
            'slug' => 'my-form',
        ];

        $form = $this->service->createForm($formData);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('my-form', $form->slug);
        $this->assertEquals('My Form', $form->name);
    }

    public function test_create_form_generates_slug_from_name_when_not_provided(): void
    {
        $formData = ['name' => 'My Contact Form'];

        $form = $this->service->createForm($formData);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('my-contact-form', $form->slug);
    }

    public function test_create_form_ensures_unique_slug(): void
    {
        Form::factory()->create(['slug' => 'test-form']);

        $form = $this->service->createForm(['name' => 'Test Form', 'slug' => 'test-form']);

        $this->assertNotEquals('test-form', $form->slug);
        $this->assertStringContainsString('test-form', $form->slug);
    }

    // --- getFormStatistics ---

    public function test_get_form_statistics_returns_array(): void
    {
        $form = Form::factory()->create();

        $stats = $this->service->getFormStatistics($form->id);

        $this->assertIsArray($stats);
    }

    // --- markEntriesAsSpam ---

    public function test_mark_entries_as_spam_returns_count(): void
    {
        $form   = Form::factory()->create();
        $entry1 = FormEntry::factory()->create(['form_id' => $form->id]);
        $entry2 = FormEntry::factory()->create(['form_id' => $form->id]);

        $count = $this->service->markEntriesAsSpam([$entry1->id, $entry2->id]);

        $this->assertEquals(2, $count);
    }

    // --- exportFormEntries ---

    public function test_export_form_entries_returns_csv_string(): void
    {
        $form = Form::factory()->create();
        FormEntry::factory()->create(['form_id' => $form->id, 'submitted_at' => now()]);

        $csv = $this->service->exportFormEntries($form->id);

        $this->assertIsString($csv);
        $this->assertStringContainsString('ID', $csv);
    }

    // --- getFields ---

    public function test_get_fields_returns_array_of_field_configs(): void
    {
        $form  = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'phone',
            'label'   => 'Phone',
            'type'    => 'text',
        ]);
        $form->load('fields');

        $fields = $this->service->getFields($form);

        $this->assertArrayHasKey('phone', $fields);
        $this->assertEquals(FormFieldType::Text, $fields['phone']['type']);
    }

    // --- renderForm ---

    public function test_render_form_returns_html(): void
    {
        $form = Form::factory()->create();

        $html = $this->service->renderForm($form);

        $this->assertStringContainsString('<form', $html);
    }

    // --- validateFormSubmission ---

    public function test_validate_form_submission_returns_validated_data(): void
    {
        $form = Form::factory()->create();

        $result = $this->service->validateFormSubmission($form, ['formData' => []]);

        $this->assertIsArray($result);
    }

    // --- getValidationRules ---

    public function test_get_validation_rules_returns_array(): void
    {
        $form = Form::factory()->create();

        $rules = $this->service->getValidationRules($form);

        $this->assertIsArray($rules);
    }

    // --- getValidationMessages ---

    public function test_get_validation_messages_returns_array(): void
    {
        $form = Form::factory()->create();

        $messages = $this->service->getValidationMessages($form);

        $this->assertIsArray($messages);
    }

    // --- getValidationAttributes ---

    public function test_get_validation_attributes_returns_array(): void
    {
        $form = Form::factory()->create();

        $attributes = $this->service->getValidationAttributes($form);

        $this->assertIsArray($attributes);
    }
}
