<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Events\FormSubmitted;
use Appsolutely\AIO\Exceptions\FormNotFoundException;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Models\FormField;
use Appsolutely\AIO\Repositories\FormEntryRepository;
use Appsolutely\AIO\Repositories\FormRepository;
use Appsolutely\AIO\Services\Contracts\DynamicFormSubmissionServiceInterface;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use PDOException;
use Psr\Log\LoggerInterface;

/**
 * Service for processing form submissions
 *
 * This service coordinates form submission workflow by composing:
 *
 * - DynamicFormValidationService: Validates form data against field rules
 * - ConnectionInterface: Handles database operations for target table insertion
 *
 * Composition pattern:
 * 1. Validates submission using DynamicFormValidationService
 * 2. Creates form entry and performs spam checking
 * 3. Optionally inserts data into target table
 * 4. Dispatches FormSubmitted event (listeners handle notifications)
 *
 * This composition provides:
 * - Separation of validation, storage, and notification concerns
 * - Reusable validation logic across different submission contexts
 * - Event-driven notification system
 */
final readonly class DynamicFormSubmissionService implements DynamicFormSubmissionServiceInterface
{
    const FORM_WRAPPER = 'formData';

    public function __construct(
        protected FormRepository $formRepository,
        protected FormEntryRepository $entryRepository,
        protected DynamicFormValidationService $validationService,
        protected ConnectionInterface $db,
        protected LoggerInterface $logger
    ) {}

    /**
     * Submit form entry
     */
    public function submitForm(string $slug, array $data, ?Request $request = null): FormEntry
    {
        $form = $this->formRepository->findBySlug($slug);

        if (! $form) {
            throw new FormNotFoundException($slug);
        }

        // Validate the submission
        $validatedData = $this->validationService->validateFormSubmission($form, [self::FORM_WRAPPER => $data])[self::FORM_WRAPPER] ?? $data;

        // Prepare entry data with fallback values
        $entryData = [
            'form_id'    => $form->id,
            'name'       => $data['name'] ?? $data['fullname'] ?? null,
            'first_name' => $data['first_name'] ?? $data['firstname'] ?? null,
            'last_name'  => $data['last_name'] ?? $data['lastname'] ?? null,
            'email'      => $data['email'] ?? null,
            'mobile'     => $data['mobile'] ?? $data['phone'] ?? $data['phone_number'] ?? null,
            'data'       => $this->prepareFormData($form, $validatedData),
        ];

        // Add request metadata if available
        if ($request) {
            $entryData['referer']    = $request->header('referer');
            $entryData['ip_address'] = client_ip($request);
            $entryData['user_agent'] = $request->header('user-agent');

            if ($request->user()) {
                $entryData['user_id'] = $request->user()->id;
            }

            // Collect meta from cookies per form config (keys are not hardcoded)
            $metaKeys = $form->meta_keys_to_collect ?? [];
            if (! empty($metaKeys) && is_array($metaKeys)) {
                $entryData['meta'] = $this->collectMetaFromCookies($metaKeys, $request);
            }
        }

        // Create the form entry
        $formEntry = $this->entryRepository->createEntryWithSpamCheck($entryData);

        // Insert into target table if specified
        if ($form->target_table) {
            $this->insertIntoTargetTable($form, $validatedData, $formEntry);
        }

        // Dispatch form submitted event (listeners will handle notifications)
        event(new FormSubmitted($form, $formEntry, $validatedData));

        return $formEntry;
    }

    /**
     * Collect meta values from cookies for the configured keys.
     *
     * Cookies may be set by Laravel (encrypted via EncryptCookies middleware)
     * or by third-party scripts (raw/unencrypted). We first try the request's
     * cookie() method which handles decryption, then fall back to $_COOKIE
     * for third-party cookies that Laravel cannot decrypt.
     *
     * @param  array<string>  $metaKeys
     * @return array<string, string>
     */
    protected function collectMetaFromCookies(array $metaKeys, Request $request): array
    {
        $meta = [];

        foreach ($metaKeys as $key) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            // Try Laravel's decrypted cookie first (handles cookies set by our middleware)
            $value = $request->cookie($key);

            // Fall back to raw $_COOKIE for third-party cookies (set by JS/external scripts)
            if ($value === null || $value === '') {
                $value = $_COOKIE[$key] ?? null;
            }

            if ($value !== null && $value !== '') {
                $meta[$key] = (string) $value;
            }
        }

        return $meta;
    }

    /**
     * Prepare form data for storage.
     * Only includes keys that exist in the submitted data so we do not store
     * fields that belong to other form types (e.g. background_image for test-drive).
     * File fields with no upload (null/empty) are omitted so optional file fields
     * do not appear in stored JSON when not used.
     */
    protected function prepareFormData(Form $form, array $data): array
    {
        $preparedData = [];

        foreach ($form->fields as $field) {
            if (! array_key_exists($field->name, $data)) {
                continue;
            }

            $value = $data[$field->name];

            // Handle file uploads: only store when a file was actually uploaded
            if ($field->type === 'file') {
                if (empty($value)) {
                    continue;
                }
                $preparedData[$field->name] = $this->handleFileUpload($value, $field);
            } else {
                $preparedData[$field->name] = $value;
            }
        }

        return $preparedData;
    }

    /**
     * Handle file upload for form field
     */
    protected function handleFileUpload($file, FormField $field): array
    {
        if (is_array($file)) {
            $uploadedFiles = [];
            foreach ($file as $singleFile) {
                $uploadedFiles[] = [
                    'name' => $singleFile->getClientOriginalName(),
                    'path' => $singleFile->store('form-uploads'),
                    'size' => $singleFile->getSize(),
                    'mime' => $singleFile->getMimeType(),
                ];
            }

            return $uploadedFiles;
        }

        return [
            'name' => $file->getClientOriginalName(),
            'path' => $file->store('form-uploads'),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ];
    }

    /**
     * Insert form data into target table
     */
    protected function insertIntoTargetTable(Form $form, array $validatedData, FormEntry $formEntry): void
    {
        if (empty($form->target_table)) {
            return;
        }

        // Get table columns to filter data
        $tableColumns = $this->getTableColumns($form->target_table);

        if (empty($tableColumns)) {
            $this->logger->warning("Target table '{$form->target_table}' does not exist or has no columns");

            return;
        }

        // Prepare data for target table
        $targetData = $this->prepareTargetTableData($validatedData, $tableColumns, $formEntry);

        if (empty($targetData)) {
            $this->logger->warning("No matching columns found for target table '{$form->target_table}'");

            return;
        }

        try {
            // Insert into target table using injected connection
            $this->db->table($form->target_table)->insert($targetData);

            $this->logger->info("Successfully inserted form data into target table '{$form->target_table}'", [
                'form_id'       => $form->id,
                'form_entry_id' => $formEntry->id,
                'target_table'  => $form->target_table,
            ]);
        } catch (QueryException|PDOException $e) {
            $this->logger->error("Failed to insert into target table '{$form->target_table}': database error", [
                'form_id'       => $form->id,
                'form_entry_id' => $formEntry->id,
                'target_table'  => $form->target_table,
                'error'         => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to insert into target table '{$form->target_table}': unexpected error", [
                'form_id'       => $form->id,
                'form_entry_id' => $formEntry->id,
                'target_table'  => $form->target_table,
                'error'         => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get table columns for the target table
     */
    protected function getTableColumns(string $tableName): array
    {
        try {
            $columns = $this->db->getSchemaBuilder()->getColumnListing($tableName);

            return $columns;
        } catch (QueryException|PDOException $e) {
            $this->logger->error("Failed to get columns for table '{$tableName}': database error", [
                'table_name' => $tableName,
                'error'      => $e->getMessage(),
            ]);

            return [];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get columns for table '{$tableName}': unexpected error", [
                'table_name' => $tableName,
                'error'      => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Prepare data for target table by filtering only existing columns
     */
    protected function prepareTargetTableData(array $validatedData, array $tableColumns, FormEntry $formEntry): array
    {
        $targetData = [];

        // Add form entry reference if column exists
        if (in_array('form_entry_id', $tableColumns)) {
            $targetData['form_entry_id'] = $formEntry->id;
        }

        // Add common contact fields if they exist in target table
        $contactFields = [
            'name'       => $formEntry->name,
            'first_name' => $formEntry->first_name,
            'last_name'  => $formEntry->last_name,
            'email'      => $formEntry->email,
            'mobile'     => $formEntry->mobile,
        ];

        foreach ($contactFields as $field => $value) {
            if (in_array($field, $tableColumns) && ! is_null($value)) {
                $targetData[$field] = $value;
            }
        }

        // Add form data fields that match table columns
        foreach ($validatedData as $fieldName => $value) {
            // Remove 'formData.' prefix if present
            $cleanFieldName = str_replace('formData.', '', $fieldName);

            if (in_array($cleanFieldName, $tableColumns) && ! isset($targetData[$cleanFieldName])) {
                // Handle different data types
                if (is_array($value)) {
                    // Convert arrays to JSON or comma-separated string based on column type
                    $targetData[$cleanFieldName] = json_encode($value);
                } else {
                    $targetData[$cleanFieldName] = $value;
                }
            }
        }

        // Add timestamps if columns exist
        $now = now();
        if (in_array('created_at', $tableColumns)) {
            $targetData['created_at'] = $now;
        }
        if (in_array('updated_at', $tableColumns)) {
            $targetData['updated_at'] = $now;
        }

        return $targetData;
    }
}
