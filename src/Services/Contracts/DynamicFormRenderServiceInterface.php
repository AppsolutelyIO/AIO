<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormField;

interface DynamicFormRenderServiceInterface
{
    /**
     * Generate HTML for form rendering
     */
    public function renderForm(Form $form, array $values = [], array $errors = []): string;

    /**
     * Render individual form field
     */
    public function renderField(FormField $field, $value = null, $error = null): string;

    /**
     * Get form fields as array for frontend rendering
     */
    public function getFields(Form $form): array;
}
