<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Tests\Unit\Models;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Models\FormField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Appsolutely\AIO\Tests\TestCase;

final class FormFieldTest extends TestCase
{
    use RefreshDatabase;

    // --- getDefaultValueAttribute ---

    public function test_default_value_returns_value_from_setting(): void
    {
        $field = FormField::factory()->make(['setting' => ['default' => 'hello']]);

        $this->assertEquals('hello', $field->default_value);
    }

    public function test_default_value_returns_null_when_not_set(): void
    {
        $field = FormField::factory()->make(['setting' => []]);

        $this->assertNull($field->default_value);
    }

    // --- getIsReadonlyAttribute ---

    public function test_is_readonly_returns_true_when_set(): void
    {
        $field = FormField::factory()->make(['setting' => ['readonly' => true]]);

        $this->assertTrue($field->is_readonly);
    }

    public function test_is_readonly_returns_false_by_default(): void
    {
        $field = FormField::factory()->make(['setting' => []]);

        $this->assertFalse($field->is_readonly);
    }

    // --- getValidationRulesAttribute ---

    public function test_validation_rules_includes_required_when_field_is_required(): void
    {
        $field = FormField::factory()->make(['type' => 'text', 'required' => true, 'setting' => []]);

        $this->assertContains('required', $field->validation_rules);
    }

    public function test_validation_rules_does_not_include_required_when_optional(): void
    {
        $field = FormField::factory()->make(['type' => 'text', 'required' => false, 'setting' => []]);

        $this->assertNotContains('required', $field->validation_rules);
    }

    public function test_validation_rules_includes_email_for_email_type(): void
    {
        $field = FormField::factory()->make(['type' => 'email', 'required' => false, 'setting' => []]);

        $this->assertContains('email', $field->validation_rules);
    }

    public function test_validation_rules_includes_numeric_for_number_type(): void
    {
        $field = FormField::factory()->make(['type' => 'number', 'required' => false, 'setting' => []]);

        $this->assertContains('numeric', $field->validation_rules);
    }

    public function test_validation_rules_includes_date_for_date_type(): void
    {
        $field = FormField::factory()->make(['type' => 'date', 'required' => false, 'setting' => []]);

        $this->assertContains('date', $field->validation_rules);
    }

    public function test_validation_rules_includes_file_for_file_type(): void
    {
        $field = FormField::factory()->make(['type' => 'file', 'required' => false, 'setting' => []]);

        $this->assertContains('file', $field->validation_rules);
    }

    public function test_validation_rules_includes_min_max_for_number_type(): void
    {
        $field = FormField::factory()->make([
            'type'     => 'number',
            'required' => false,
            'setting'  => ['min' => 5, 'max' => 100],
        ]);

        $rules = $field->validation_rules;
        $this->assertContains('min:5', $rules);
        $this->assertContains('max:100', $rules);
    }

    public function test_validation_rules_returns_array(): void
    {
        $field = FormField::factory()->make(['type' => 'text', 'required' => false, 'setting' => []]);

        $this->assertIsArray($field->validation_rules);
    }

    // --- getSupportsMultipleValuesAttribute ---

    public function test_supports_multiple_values_for_checkbox(): void
    {
        $field = FormField::factory()->make(['type' => FormFieldType::Checkbox->value]);

        $this->assertTrue($field->supports_multiple_values);
    }

    public function test_supports_multiple_values_for_multiple_select(): void
    {
        $field = FormField::factory()->make(['type' => FormFieldType::MultipleSelect->value]);

        $this->assertTrue($field->supports_multiple_values);
    }

    public function test_does_not_support_multiple_values_for_text(): void
    {
        $field = FormField::factory()->make(['type' => FormFieldType::Text->value]);

        $this->assertFalse($field->supports_multiple_values);
    }

    public function test_does_not_support_multiple_values_for_email(): void
    {
        $field = FormField::factory()->make(['type' => FormFieldType::Email->value]);

        $this->assertFalse($field->supports_multiple_values);
    }
}
