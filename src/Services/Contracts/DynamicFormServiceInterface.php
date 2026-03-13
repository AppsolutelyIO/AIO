<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

interface DynamicFormServiceInterface
{
    /**
     * Get form by slug with fields
     */
    public function getFormBySlug(string $slug): ?Form;

    /**
     * Create a new form with fields
     */
    public function createForm(array $formData, array $fieldsData = []): Form;

    /**
     * Submit form entry
     */
    public function submitForm(string $slug, array $data, ?Request $request = null): FormEntry;

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

    /**
     * Generate HTML for form rendering
     */
    public function renderForm(Form $form, array $values = [], array $errors = []): string;

    /**
     * Get form statistics
     */
    public function getFormStatistics(int $formId): array;

    /**
     * Mark entries as spam
     */
    public function markEntriesAsSpam(array $entryIds): int;

    /**
     * Export form entries to CSV
     */
    public function exportFormEntries(int $formId): string;

    /**
     * Get form fields as array for frontend rendering
     */
    public function getFields(Form $form): array;
}
