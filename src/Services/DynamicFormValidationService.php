<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Services\Contracts\DynamicFormValidationServiceInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final readonly class DynamicFormValidationService implements DynamicFormValidationServiceInterface
{
    const FORM_WRAPPER = 'formData';

    /**
     * Validate form submission
     */
    public function validateFormSubmission(Form $form, array $data): array
    {
        $rules      = $this->getValidationRules($form);
        $messages   = $this->getValidationMessages($form);
        $attributes = $this->getValidationAttributes($form);

        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Get validation rules for form
     */
    public function getValidationRules(Form $form): array
    {
        $rules = [];

        foreach ($form->fields as $field) {
            $fieldRules = $field->validation_rules;

            if (! empty($fieldRules)) {
                $rules[self::FORM_WRAPPER . ".{$field->name}"] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Get validation messages for form
     */
    public function getValidationMessages(Form $form): array
    {
        $messages = [];

        foreach ($form->fields as $field) {
            $fieldKey = self::FORM_WRAPPER . ".{$field->name}";
            $label    = $field->label;

            // Get validation rules for this field
            $validationRules = $field->validation_rules ?? [];
            if (empty($validationRules)) {
                continue;
            }

            // Use Laravel's built-in validation message generation
            // Create a temporary validator to get default messages
            $validator = Validator::make(
                [$field->name => null], // dummy data
                [$field->name => $validationRules], // rules
                [], // no custom messages
                [$field->name => $label] // attributes
            );

            // Extract the default messages and remap to our field key format
            $defaultMessages = $validator->getMessageBag()->getMessages();

            foreach ($validationRules as $rule) {
                $ruleName = is_string($rule) ? explode(':', $rule)[0] : $rule;

                // Generate the Laravel message key and our custom key
                $laravelKey = "{$field->name}.{$ruleName}";
                $ourKey     = "{$fieldKey}.{$ruleName}";

                // Try to get Laravel's default message for this rule
                $defaultMessage = trans("validation.{$ruleName}", [
                    'attribute' => $label,
                    'value'     => '', // placeholder
                ]);

                // If Laravel has a default message and it's not the translation key, use it
                if ($defaultMessage !== "validation.{$ruleName}") {
                    $messages[$ourKey] = $defaultMessage;
                } else {
                    // Fallback to custom message for field-specific rules
                    $messages[$ourKey] = "The {$label} field is invalid.";
                }
            }
        }

        return $messages;
    }

    /**
     * Get validation attributes for form
     */
    public function getValidationAttributes(Form $form): array
    {
        $attributes = [];

        foreach ($form->fields as $field) {
            $attributes[$field->name] = $field->label;
        }

        return $attributes;
    }
}
