<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Form;
use Illuminate\Validation\ValidationException;

interface DynamicFormValidationServiceInterface
{
    /**
     * Validate form submission
     *
     * @throws ValidationException
     */
    public function validateFormSubmission(Form $form, array $data): array;

    /**
     * Get validation rules for form
     */
    public function getValidationRules(Form $form): array;

    /**
     * Get validation messages for form
     */
    public function getValidationMessages(Form $form): array;

    /**
     * Get validation attributes for form
     */
    public function getValidationAttributes(Form $form): array;
}
