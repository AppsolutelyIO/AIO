<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Services;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Services\DynamicFormValidationService;
use Appsolutely\AIO\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

final class DynamicFormValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private DynamicFormValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DynamicFormValidationService::class);
    }

    // --- getValidationRules ---

    public function test_get_validation_rules_returns_empty_for_no_fields(): void
    {
        $form = Form::factory()->create();

        $rules = $this->service->getValidationRules($form);

        $this->assertEmpty($rules);
    }

    public function test_get_validation_rules_includes_required_for_required_fields(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id'  => $form->id,
            'name'     => 'full_name',
            'type'     => 'text',
            'required' => true,
        ]);

        $rules = $this->service->getValidationRules($form->load('fields'));

        $this->assertArrayHasKey('formData.full_name', $rules);
        $this->assertContains('required', $rules['formData.full_name']);
    }

    public function test_get_validation_rules_includes_email_for_email_type(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id'  => $form->id,
            'name'     => 'email_address',
            'type'     => 'email',
            'required' => true,
        ]);

        $rules = $this->service->getValidationRules($form->load('fields'));

        $this->assertArrayHasKey('formData.email_address', $rules);
        $this->assertContains('email', $rules['formData.email_address']);
    }

    public function test_get_validation_rules_skips_optional_fields_with_no_rules(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id'  => $form->id,
            'name'     => 'optional_field',
            'type'     => 'text',
            'required' => false,
        ]);

        $rules = $this->service->getValidationRules($form->load('fields'));

        $this->assertArrayNotHasKey('formData.optional_field', $rules);
    }

    public function test_get_validation_rules_uses_form_wrapper(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id'  => $form->id,
            'name'     => 'phone',
            'type'     => 'text',
            'required' => true,
        ]);

        $rules = $this->service->getValidationRules($form->load('fields'));

        foreach (array_keys($rules) as $key) {
            $this->assertStringStartsWith('formData.', $key);
        }
    }

    public function test_get_validation_rules_handles_multiple_fields(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id, 'name' => 'name', 'type' => 'text', 'required' => true]);
        FormField::factory()->create(['form_id' => $form->id, 'name' => 'email', 'type' => 'email', 'required' => true]);

        $rules = $this->service->getValidationRules($form->load('fields'));

        $this->assertArrayHasKey('formData.name', $rules);
        $this->assertArrayHasKey('formData.email', $rules);
    }

    // --- getValidationAttributes ---

    public function test_get_validation_attributes_maps_name_to_label(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name'    => 'first_name',
            'label'   => 'First Name',
        ]);

        $attributes = $this->service->getValidationAttributes($form->load('fields'));

        $this->assertArrayHasKey('first_name', $attributes);
        $this->assertEquals('First Name', $attributes['first_name']);
    }

    public function test_get_validation_attributes_returns_empty_for_no_fields(): void
    {
        $form = Form::factory()->create();

        $attributes = $this->service->getValidationAttributes($form);

        $this->assertEmpty($attributes);
    }

    public function test_get_validation_attributes_includes_all_fields(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id, 'name' => 'field_a', 'label' => 'Field A']);
        FormField::factory()->create(['form_id' => $form->id, 'name' => 'field_b', 'label' => 'Field B']);

        $attributes = $this->service->getValidationAttributes($form->load('fields'));

        $this->assertArrayHasKey('field_a', $attributes);
        $this->assertArrayHasKey('field_b', $attributes);
    }

    // --- validateFormSubmission ---

    public function test_validate_form_submission_passes_with_valid_data(): void
    {
        $form  = Form::factory()->create();
        $field = FormField::factory()->create([
            'form_id'  => $form->id,
            'name'     => 'full_name',
            'type'     => 'text',
            'required' => true,
        ]);

        $data   = ['formData' => ['full_name' => 'John Doe']];
        $result = $this->service->validateFormSubmission($form->load('fields'), $data);

        $this->assertNotEmpty($result);
    }

    public function test_validate_form_submission_throws_for_missing_required_field(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id'  => $form->id,
            'name'     => 'full_name',
            'type'     => 'text',
            'required' => true,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->validateFormSubmission($form->load('fields'), ['formData' => []]);
    }

    public function test_validate_form_submission_throws_for_invalid_email(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id'  => $form->id,
            'name'     => 'email',
            'type'     => 'email',
            'required' => true,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->validateFormSubmission(
            $form->load('fields'),
            ['formData' => ['email' => 'not-an-email']]
        );
    }

    public function test_validate_form_submission_passes_for_form_with_no_fields(): void
    {
        $form = Form::factory()->create();

        $result = $this->service->validateFormSubmission($form, ['formData' => []]);

        $this->assertIsArray($result);
    }
}
