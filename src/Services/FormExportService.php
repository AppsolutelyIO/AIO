<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\FormEntrySpamStatus;
use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Repositories\FormEntryRepository;
use Appsolutely\AIO\Repositories\FormRepository;
use Appsolutely\AIO\Services\Contracts\FormExportServiceInterface;
use Illuminate\Support\Facades\Storage;

final readonly class FormExportService implements FormExportServiceInterface
{
    public function __construct(
        protected FormRepository $formRepository,
        protected FormEntryRepository $entryRepository
    ) {}

    /**
     * Export form entries to CSV file
     */
    public function exportFormEntriesToCsv(int $formId, bool $includeSpam = false, bool $includeMetadata = false): string
    {
        $form    = $this->formRepository->find($formId);
        $entries = $this->entryRepository->getEntriesByForm($formId, $includeSpam);

        $csv = $this->generateCsv($form, $entries, $includeMetadata);

        $dateFolder = now()->format('Ymd');
        $filename   = $this->generateFilename($form->slug, 'csv');
        $filepath   = "export/{$dateFolder}/{$filename}";

        // Ensure export directory exists
        Storage::disk('exports')->makeDirectory("export/{$dateFolder}");

        // Save CSV file
        Storage::disk('exports')->put($filepath, $csv);

        // Return the full path to the file
        return Storage::disk('exports')->path($filepath);
    }

    /**
     * Export form entries to Excel file
     */
    public function exportFormEntriesToExcel(int $formId, bool $includeSpam = false, bool $includeMetadata = false): string
    {
        $form    = $this->formRepository->find($formId);
        $entries = $this->entryRepository->getEntriesByForm($formId, $includeSpam);

        $excel = $this->generateExcel($form, $entries, $includeMetadata);

        $dateFolder = now()->format('Ymd');
        $filename   = $this->generateFilename($form->slug, 'xlsx');
        $filepath   = "export/{$dateFolder}/{$filename}";

        // Ensure export directory exists
        Storage::disk('exports')->makeDirectory("export/{$dateFolder}");

        // Save Excel file
        Storage::disk('exports')->put($filepath, $excel);

        // Return the full path to the file
        return Storage::disk('exports')->path($filepath);
    }

    /**
     * Generate CSV content from form entries
     */
    protected function generateCsv(Form $form, $entries, bool $includeMetadata = false): string
    {
        $output = fopen('php://temp', 'r+');

        // Standard field mappings (column name -> field name in data)
        $standardFields = [
            'first_name' => 'First Name',
            'last_name'  => 'Last Name',
            'email'      => 'Email',
            'mobile'     => 'Mobile',
        ];

        // Build headers - start with standard fields
        $headers = ['ID', 'Submitted At'];
        foreach ($standardFields as $fieldName => $label) {
            $headers[] = $label;
        }
        $headers[] = 'Is Spam';

        // Add dynamic form fields (excluding duplicates of standard fields)
        $dynamicFields = [];
        foreach ($form->fields as $field) {
            if (! in_array($field->name, array_keys($standardFields))) {
                $headers[]       = $field->label;
                $dynamicFields[] = $field;
            }
        }

        // Add metadata fields (if requested)
        if ($includeMetadata) {
            $headers = array_merge($headers, ['IP Address', 'User Agent', 'Referer', 'User ID', 'User Name']);
        }

        fputcsv($output, $headers);

        // Data rows
        foreach ($entries as $entry) {
            $row = [
                $entry->id,
                $entry->submitted_at->format('Y-m-d H:i:s'),
            ];

            // Add standard field values (prioritize data column, fallback to actual column)
            foreach ($standardFields as $fieldName => $label) {
                $value = $entry->getFieldValue($fieldName) ?? $entry->{$fieldName} ?? '';
                $row[] = $value;
            }

            // Add spam status
            $row[] = FormEntrySpamStatus::toYesNoFrom($entry->is_spam);

            // Add dynamic field values
            foreach ($dynamicFields as $field) {
                $value = $entry->getFieldValue($field->name);
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $row[] = $value ?? '';
            }

            // Add metadata (if requested)
            if ($includeMetadata) {
                $row[] = $entry->ip_address ?? '';
                $row[] = $entry->user_agent ?? '';
                $row[] = $entry->referer ?? '';
                $row[] = $entry->user_id ?? '';
                $row[] = $entry->user ? $entry->user->name : '';
            }

            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate Excel content from form entries (XLSX format)
     */
    protected function generateExcel(Form $form, $entries, bool $includeMetadata = false): string
    {
        // Create a simple XLSX file using SpreadsheetML format
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . "\n";
        $xml .= '  <sheets>' . "\n";
        $xml .= '    <sheet name="Form Entries" sheetId="1" r:id="rId1"/>' . "\n";
        $xml .= '  </sheets>' . "\n";
        $xml .= '</workbook>' . "\n";

        // For simplicity, we'll generate a CSV-like format wrapped in XML
        // For a full Excel implementation, consider using PhpSpreadsheet package
        // For now, let's use a tab-delimited format that Excel can open

        $content = $this->generateCsv($form, $entries, $includeMetadata);

        // Convert CSV to tab-delimited for better Excel compatibility
        $content = str_replace(',', "\t", $content);

        return $content;
    }

    /**
     * Generate filename for export
     */
    protected function generateFilename(string $formSlug, string $extension = 'csv'): string
    {
        $date = now()->format('Ymd');

        return "Form-{$formSlug}-{$date}.{$extension}";
    }
}
