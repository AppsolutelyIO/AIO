<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\PageBlock;
use Appsolutely\AIO\Services\Contracts\PageBlockSchemaServiceInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class PageBlockSchemaService implements PageBlockSchemaServiceInterface
{
    /**
     * Get schema for a block
     */
    public function getBlockSchema(PageBlock $block): array
    {
        if ($block->schema === null) {
            return [];
        }

        if (! is_array($block->schema)) {
            return json_decode($block->schema, true) ?? [];
        }

        return $block->schema;
    }

    /**
     * Validate schema values against block schema
     */
    public function validateSchemaValues(array $schema, array $values): array
    {
        $rules    = $this->buildValidationRules($schema);
        $messages = $this->buildValidationMessages($schema);

        $validator = Validator::make($values, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get default values from schema
     */
    public function getDefaultValues(array $schema): array
    {
        $defaults = [];

        foreach ($schema as $fieldName => $fieldConfig) {
            if (isset($fieldConfig['default'])) {
                $defaults[$fieldName] = $fieldConfig['default'];
            }

            if ($fieldConfig['type'] === 'table' && isset($fieldConfig['fields'])) {
                $defaults[$fieldName] = [];
            }

            if ($fieldConfig['type'] === 'object' && isset($fieldConfig['fields'])) {
                $defaults[$fieldName] = $this->getDefaultValues($fieldConfig['fields']);
            }
        }

        return $defaults;
    }

    /**
     * Merge schema values with defaults
     */
    public function mergeWithDefaults(array $schema, array $values): array
    {
        $defaults = $this->getDefaultValues($schema);

        return array_merge($defaults, $values);
    }

    /**
     * Build Laravel validation rules from schema
     */
    private function buildValidationRules(array $schema): array
    {
        $rules = [];

        foreach ($schema as $fieldName => $fieldConfig) {
            $fieldRules = $this->getFieldValidationRules($fieldConfig);

            if (! empty($fieldRules)) {
                $rules[$fieldName] = $fieldRules;
            }

            // Handle nested fields for table and object types
            if (in_array($fieldConfig['type'], ['table', 'object']) && isset($fieldConfig['fields'])) {
                $nestedRules = $this->buildValidationRules($fieldConfig['fields']);
                foreach ($nestedRules as $nestedField => $nestedRule) {
                    $rules["{$fieldName}.*.{$nestedField}"] = $nestedRule;
                }
            }
        }

        return $rules;
    }

    /**
     * Get validation rules for a specific field type
     */
    private function getFieldValidationRules(array $fieldConfig): array
    {
        $rules = [];

        // Required validation
        if (Arr::get($fieldConfig, 'required', false)) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-specific validation
        switch ($fieldConfig['type']) {
            case 'text':
            case 'textarea':
                $rules[] = 'string';
                if (isset($fieldConfig['max_length'])) {
                    $rules[] = 'max:' . $fieldConfig['max_length'];
                }
                if (isset($fieldConfig['pattern'])) {
                    $rules[] = 'regex:' . $fieldConfig['pattern'];
                }
                break;

            case 'number':
                $rules[] = 'numeric';
                if (isset($fieldConfig['min'])) {
                    $rules[] = 'min:' . $fieldConfig['min'];
                }
                if (isset($fieldConfig['max'])) {
                    $rules[] = 'max:' . $fieldConfig['max'];
                }
                if (isset($fieldConfig['step'])) {
                    $rules[] = 'numeric';
                }
                break;

            case 'email':
                $rules[] = 'email';
                break;

            case 'url':
                $rules[] = 'url';
                break;

            case 'image':
                $rules[] = 'file';
                $rules[] = 'image';
                if (isset($fieldConfig['max_size'])) {
                    $rules[] = 'max:' . $this->parseFileSize($fieldConfig['max_size']);
                }
                break;

            case 'file':
                $rules[] = 'file';
                if (isset($fieldConfig['allowed_types'])) {
                    $rules[] = 'mimes:' . implode(',', $fieldConfig['allowed_types']);
                }
                break;

            case 'table':
                $rules[] = 'array';
                if (isset($fieldConfig['max_items'])) {
                    $rules[] = 'max:' . $fieldConfig['max_items'];
                }
                if (isset($fieldConfig['min_items'])) {
                    $rules[] = 'min:' . $fieldConfig['min_items'];
                }
                break;

            case 'select':
                if (isset($fieldConfig['options'])) {
                    $options = array_column($fieldConfig['options'], 'value');
                    $rules[] = 'in:' . implode(',', $options);
                }
                break;

            case 'multiSelect':
                $rules[] = 'array';
                if (isset($fieldConfig['options'])) {
                    $options = array_column($fieldConfig['options'], 'value');
                    $rules[] = 'in:' . implode(',', $options);
                }
                if (isset($fieldConfig['max_selections'])) {
                    $rules[] = 'max:' . $fieldConfig['max_selections'];
                }
                break;

            case 'boolean':
                $rules[] = 'boolean';
                break;

            case 'datetime':
            case 'date':
                $rules[] = 'date';
                break;

            case 'color':
                $rules[] = 'string';
                $rules[] = 'regex:/^#[0-9A-F]{6}$/i';
                break;
        }

        return $rules;
    }

    /**
     * Parse file size string to bytes
     */
    private function parseFileSize(string $size): int
    {
        $units = ['B' => 1, 'KB' => 1024, 'MB' => 1024 * 1024, 'GB' => 1024 * 1024 * 1024];
        $unit  = strtoupper(substr($size, -2));
        $value = (int) substr($size, 0, -2);

        return $value * ($units[$unit] ?? 1);
    }

    /**
     * Build validation messages
     */
    private function buildValidationMessages(array $schema): array
    {
        $messages = [];

        foreach ($schema as $fieldName => $fieldConfig) {
            $label = $fieldConfig['label'] ?? $fieldName;

            $messages["{$fieldName}.required"] = "The {$label} field is required.";
            $messages["{$fieldName}.string"]   = "The {$label} must be a string.";
            $messages["{$fieldName}.numeric"]  = "The {$label} must be a number.";
            $messages["{$fieldName}.email"]    = "The {$label} must be a valid email address.";
            $messages["{$fieldName}.url"]      = "The {$label} must be a valid URL.";
            $messages["{$fieldName}.file"]     = "The {$label} must be a file.";
            $messages["{$fieldName}.image"]    = "The {$label} must be an image.";
            $messages["{$fieldName}.array"]    = "The {$label} must be an array.";
            $messages["{$fieldName}.boolean"]  = "The {$label} must be true or false.";
            $messages["{$fieldName}.date"]     = "The {$label} must be a valid date.";
            $messages["{$fieldName}.in"]       = "The selected {$label} is invalid.";
        }

        return $messages;
    }

    /**
     * Generate form configuration for admin interface
     */
    public function generateFormConfig(array $schema): array
    {
        $formConfig = [];

        foreach ($schema as $fieldName => $fieldConfig) {
            $formConfig[$fieldName] = [
                'type'        => $fieldConfig['type'],
                'label'       => $fieldConfig['label'] ?? $fieldName,
                'description' => $fieldConfig['description'] ?? '',
                'required'    => $fieldConfig['required'] ?? false,
                'default'     => $fieldConfig['default'] ?? null,
                'options'     => $fieldConfig['options'] ?? [],
                'validation'  => $this->extractValidationRules($fieldConfig),
            ];

            // Handle nested fields
            if (in_array($fieldConfig['type'], ['table', 'object']) && isset($fieldConfig['fields'])) {
                $formConfig[$fieldName]['fields'] = $this->generateFormConfig($fieldConfig['fields']);
            }
        }

        return $formConfig;
    }

    /**
     * Extract validation rules for form display
     */
    private function extractValidationRules(array $fieldConfig): array
    {
        $rules = [];

        if (isset($fieldConfig['max_length'])) {
            $rules['max_length'] = $fieldConfig['max_length'];
        }

        if (isset($fieldConfig['min'])) {
            $rules['min'] = $fieldConfig['min'];
        }

        if (isset($fieldConfig['max'])) {
            $rules['max'] = $fieldConfig['max'];
        }

        if (isset($fieldConfig['pattern'])) {
            $rules['pattern'] = $fieldConfig['pattern'];
        }

        if (isset($fieldConfig['max_size'])) {
            $rules['max_size'] = $fieldConfig['max_size'];
        }

        if (isset($fieldConfig['allowed_types'])) {
            $rules['allowed_types'] = $fieldConfig['allowed_types'];
        }

        return $rules;
    }
}
