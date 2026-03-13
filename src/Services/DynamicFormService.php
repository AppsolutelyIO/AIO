<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Repositories\FormEntryRepository;
use Appsolutely\AIO\Repositories\FormFieldRepository;
use Appsolutely\AIO\Repositories\FormRepository;
use Appsolutely\AIO\Services\Contracts\DynamicFormServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Facade/Orchestrator service for dynamic form operations
 *
 * This service composes multiple specialized services to provide a unified interface
 * for form management. It delegates to:
 *
 * - DynamicFormRenderService: Handles form HTML rendering and field configuration
 * - DynamicFormValidationService: Manages validation rules, messages, and attributes
 * - DynamicFormSubmissionService: Processes form submissions and triggers notifications
 * - DynamicFormExportService: Handles CSV export of form entries
 *
 * This composition pattern allows for:
 * - Single Responsibility: Each composed service handles one aspect of forms
 * - Testability: Services can be tested independently
 * - Flexibility: Services can be swapped or extended without affecting the facade
 */
final readonly class DynamicFormService implements DynamicFormServiceInterface
{
    public function __construct(
        protected FormRepository $formRepository,
        protected FormFieldRepository $fieldRepository,
        protected FormEntryRepository $entryRepository,
        protected DynamicFormRenderService $renderService,
        protected DynamicFormValidationService $validationService,
        protected DynamicFormSubmissionService $submissionService,
        protected DynamicFormExportService $exportService
    ) {}

    /**
     * Get form by slug with fields
     */
    public function getFormBySlug(string $slug): ?Form
    {
        return $this->formRepository->findBySlug($slug);
    }

    /**
     * Create a new form with fields
     */
    public function createForm(array $formData, array $fieldsData = []): Form
    {
        if (empty($formData['slug'])) {
            $formData['slug'] = Str::slug($formData['name']);
        }

        $formData['slug'] = $this->ensureUniqueSlug($formData['slug']);

        return $this->formRepository->createWithFields($formData, $fieldsData);
    }

    /**
     * Submit form entry
     */
    public function submitForm(string $slug, array $data, ?Request $request = null): FormEntry
    {
        return $this->submissionService->submitForm($slug, $data, $request);
    }

    /**
     * Validate form submission
     *
     * @throws ValidationException
     */
    public function validateFormSubmission(Form $form, array $data): array
    {
        return $this->validationService->validateFormSubmission($form, $data);
    }

    /**
     * Get validation rules for form
     */
    public function getValidationRules(Form $form): array
    {
        return $this->validationService->getValidationRules($form);
    }

    /**
     * Get validation messages for form
     */
    public function getValidationMessages(Form $form): array
    {
        return $this->validationService->getValidationMessages($form);
    }

    /**
     * Get validation attributes for form
     */
    public function getValidationAttributes(Form $form): array
    {
        return $this->validationService->getValidationAttributes($form);
    }

    /**
     * Generate HTML for form rendering
     */
    public function renderForm(Form $form, array $values = [], array $errors = []): string
    {
        return $this->renderService->renderForm($form, $values, $errors);
    }

    /**
     * Get form statistics
     */
    public function getFormStatistics(int $formId): array
    {
        return $this->entryRepository->getFormStats($formId);
    }

    /**
     * Mark entries as spam
     */
    public function markEntriesAsSpam(array $entryIds): int
    {
        return $this->entryRepository->markAsSpam($entryIds);
    }

    /**
     * Export form entries to CSV
     */
    public function exportFormEntries(int $formId): string
    {
        return $this->exportService->exportFormEntries($formId);
    }

    /**
     * Ensure unique slug
     */
    protected function ensureUniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $originalSlug = $slug;
        $counter      = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists
     */
    protected function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Form::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get form fields as array for frontend rendering
     */
    public function getFields(Form $form): array
    {
        return $this->renderService->getFields($form);
    }
}
