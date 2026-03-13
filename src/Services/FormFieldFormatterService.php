<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\FormFieldType;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;

/**
 * Service for formatting form field values for display in notifications
 *
 * Handles formatting of form entry data for both HTML (email) and plain text output.
 * Supports various field types including files, checkboxes, multiple selects, booleans, and URLs.
 */
final readonly class FormFieldFormatterService
{
    /**
     * Prepare complete notification data array for form submission
     *
     * @return array<string, mixed>
     */
    public function prepareNotificationData(Form $form, FormEntry $entry): array
    {
        return [
            'entry_id'         => $entry->id,
            'form_id'          => $form->id,
            'form_name'        => $form->name,
            'user_name'        => $entry->getUserName(),
            'form_fields_html' => $this->formatFieldsAsHtml($form, $entry),
            'form_fields_text' => $this->formatFieldsAsText($form, $entry),
        ];
    }

    /**
     * Format form fields as HTML table rows
     */
    public function formatFieldsAsHtml(Form $form, FormEntry $entry): string
    {
        $html = '<table id="form-fields-table">' . "\n";

        foreach ($form->fields as $field) {
            $value = $this->getFieldValue($entry, $field->name);

            if ($value === null || $value === '') {
                continue;
            }

            $formattedValue = $this->formatValue($value, $field->type);

            $html .= '    <tr>' . "\n";
            $html .= '        <td class="form-field-label">' . htmlspecialchars($field->label) . ':</td>' . "\n";
            $html .= '        <td class="form-field-value">' . $formattedValue . '</td>' . "\n";
            $html .= '    </tr>' . "\n";
        }

        $html .= '</table>';

        return $html;
    }

    /**
     * Format form fields as plain text
     */
    public function formatFieldsAsText(Form $form, FormEntry $entry): string
    {
        $text = '';

        foreach ($form->fields as $field) {
            $value = $this->getFieldValue($entry, $field->name);

            if ($value === null || $value === '') {
                continue;
            }

            $formattedValue = $this->formatValueText($value, $field->type);
            $text .= $field->label . ': ' . $formattedValue . "\n";
        }

        return $text ? "\n" . $text : '';
    }

    /**
     * Get field value from entry (checks direct properties first, then data JSON)
     */
    public function getFieldValue(FormEntry $entry, string $fieldName): mixed
    {
        $directProperties = ['name', 'email', 'mobile', 'first_name', 'last_name'];

        if (in_array($fieldName, $directProperties)) {
            $value = $entry->{$fieldName};
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $entry->getFieldValue($fieldName);
    }

    /**
     * Format a value for HTML display based on field type
     */
    public function formatValue(mixed $value, string|FormFieldType $fieldType): string
    {
        $fieldType = $fieldType instanceof FormFieldType ? $fieldType->value : $fieldType;

        // Handle file uploads
        if ($fieldType === 'file') {
            if (is_array($value)) {
                $files = [];
                foreach ($value as $file) {
                    if (is_array($file) && isset($file['name'])) {
                        $files[] = htmlspecialchars($file['name']);
                    } else {
                        $files[] = htmlspecialchars((string) $file);
                    }
                }

                return implode(', ', $files);
            }

            return htmlspecialchars((string) $value);
        }

        // Handle checkboxes and multiple selects
        if (in_array($fieldType, ['checkbox', 'multiple_select']) || is_array($value)) {
            if (is_array($value)) {
                return htmlspecialchars(implode(', ', $value));
            }
        }

        if (is_bool($value)) {
            return $value ? '<span style="color: #27ae60;">✓ Yes</span>' : '<span style="color: #e74c3c;">✗ No</span>';
        }

        if (is_null($value)) {
            return '<span style="color: #999;">—</span>';
        }

        // Check if it's a URL
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return '<a href="' . htmlspecialchars($value) . '" style="color: #3498db; text-decoration: none;">' . htmlspecialchars($value) . '</a>';
        }

        return htmlspecialchars((string) $value);
    }

    /**
     * Format a value for plain text display based on field type
     */
    public function formatValueText(mixed $value, string|FormFieldType $fieldType): string
    {
        $fieldType = $fieldType instanceof FormFieldType ? $fieldType->value : $fieldType;

        // Handle file uploads
        if ($fieldType === 'file') {
            if (is_array($value)) {
                $files = [];
                foreach ($value as $file) {
                    if (is_array($file) && isset($file['name'])) {
                        $files[] = $file['name'];
                    } else {
                        $files[] = (string) $file;
                    }
                }

                return implode(', ', $files);
            }

            return (string) $value;
        }

        // Handle checkboxes and multiple selects
        if (in_array($fieldType, ['checkbox', 'multiple_select']) || is_array($value)) {
            if (is_array($value)) {
                return implode(', ', $value);
            }
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_null($value)) {
            return '—';
        }

        return (string) $value;
    }
}
