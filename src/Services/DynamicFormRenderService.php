<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Services\Contracts\DynamicFormRenderServiceInterface;

final readonly class DynamicFormRenderService implements DynamicFormRenderServiceInterface
{
    /**
     * Generate HTML for form rendering
     */
    public function renderForm(Form $form, array $values = [], array $errors = []): string
    {
        $html = "<form method='POST' action='' enctype='multipart/form-data'>";
        $html .= csrf_field();

        foreach ($form->fields as $field) {
            $html .= $this->renderField($field, $values[$field->name] ?? null, $errors[$field->name] ?? null);
        }

        $html .= "<button type='submit' class='btn btn-primary'>Submit</button>";
        $html .= '</form>';

        return $html;
    }

    /**
     * Render individual form field
     */
    public function renderField(FormField $field, $value = null, $error = null): string
    {
        $value    = $value ?? $field->default_value;
        $required = $field->required ? 'required' : '';
        $readonly = $field->is_readonly ? 'readonly' : '';

        $typeValue = $field->type->value;

        // Hidden fields don't need wrapper, label, or error display
        if ($field->type === FormFieldType::Hidden) {
            return "<input type='hidden' id='{$field->name}' name='{$field->name}' value='{$value}'>";
        }

        $html = "<div class='form-group mb-3'>";
        $html .= "<label for='{$field->name}' class='form-label'>{$field->label}";

        if ($field->required) {
            $html .= " <span class='text-danger'>*</span>";
        }

        $html .= '</label>';

        switch ($field->type) {
            case FormFieldType::Text:
            case FormFieldType::Email:
            case FormFieldType::Number:
                $html .= "<input type='{$typeValue}' id='{$field->name}' name='{$field->name}' class='form-control' value='{$value}' placeholder='{$field->placeholder}' {$required} {$readonly}>";
                break;

            case FormFieldType::Textarea:
                $html .= "<textarea id='{$field->name}' name='{$field->name}' class='form-control' placeholder='{$field->placeholder}' {$required} {$readonly}>{$value}</textarea>";
                break;

            case FormFieldType::Select:
                $html .= "<select id='{$field->name}' name='{$field->name}' class='form-control' {$required}>";
                $html .= "<option value=''>Choose...</option>";
                foreach ($field->field_options as $option) {
                    $selected = $value === $option ? 'selected' : '';
                    $html .= "<option value='{$option}' {$selected}>{$option}</option>";
                }
                $html .= '</select>';
                break;

            case FormFieldType::Radio:
                foreach ($field->field_options as $option) {
                    $checked = $value === $option ? 'checked' : '';
                    $html .= "<div class='form-check'>";
                    $html .= "<input type='radio' id='{$field->name}_{$option}' name='{$field->name}' value='{$option}' class='form-check-input' {$checked} {$required}>";
                    $html .= "<label for='{$field->name}_{$option}' class='form-check-label'>{$option}</label>";
                    $html .= '</div>';
                }
                break;

            case FormFieldType::Checkbox:
                foreach ($field->field_options as $option) {
                    $checked = is_array($value) && in_array($option, $value) ? 'checked' : '';
                    $html .= "<div class='form-check'>";
                    $html .= "<input type='checkbox' id='{$field->name}_{$option}' name='{$field->name}[]' value='{$option}' class='form-check-input' {$checked}>";
                    $html .= "<label for='{$field->name}_{$option}' class='form-check-label'>{$option}</label>";
                    $html .= '</div>';
                }
                break;

            case FormFieldType::File:
                $multiple = $field->supports_multiple_values ? 'multiple' : '';
                $html .= "<input type='file' id='{$field->name}' name='{$field->name}" . ($multiple ? '[]' : '') . "' class='form-control' {$required} {$multiple}>";
                break;
        }

        if ($error) {
            $html .= "<div class='text-danger mt-1'>{$error}</div>";
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get form fields as array for frontend rendering
     */
    public function getFields(Form $form): array
    {
        $fields = [];
        foreach ($form->fields->sortBy('sort') as $field) {
            $fields[$field->name] = [
                'type'        => $field->type,
                'label'       => $field->label,
                'placeholder' => $field->placeholder,
                'required'    => $field->required,
                'validation'  => $field->validation_rules,
                'options'     => $field->options ?? [],
                'default'     => $field->default_value,
                'rows'        => $field->setting['rows'] ?? 4,
            ];
        }

        return $fields;
    }
}
