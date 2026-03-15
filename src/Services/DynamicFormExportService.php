<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Repositories\FormEntryRepository;
use Appsolutely\AIO\Repositories\FormRepository;
use Appsolutely\AIO\Services\Contracts\DynamicFormExportServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class DynamicFormExportService implements DynamicFormExportServiceInterface
{
    public function __construct(
        protected FormRepository $formRepository,
        protected FormEntryRepository $entryRepository
    ) {}

    /**
     * Export form entries to CSV
     */
    public function exportFormEntries(int $formId): string
    {
        $form    = $this->formRepository->find($formId);
        $entries = $this->entryRepository->getEntriesByForm($formId, false);

        $output = fopen('php://temp', 'r+');

        // Headers: base + form fields + meta keys (union of all entry meta keys)
        $metaKeys = [];
        foreach ($entries as $entry) {
            $meta     = $entry->meta ?? [];
            $metaKeys = array_unique(array_merge($metaKeys, array_keys($meta)));
        }
        sort($metaKeys);

        $headers = ['ID', 'Submitted At', 'First Name', 'Last Name', 'Email', 'Mobile'];
        foreach ($form->fields as $field) {
            $headers[] = $field->label;
        }
        foreach ($metaKeys as $key) {
            $headers[] = 'meta_' . $key;
        }
        fputcsv($output, $headers);

        // Data rows
        foreach ($entries as $entry) {
            $row = [
                $entry->id,
                $entry->submitted_at->format('Y-m-d H:i:s'),
                $entry->first_name,
                $entry->last_name,
                $entry->email,
                $entry->mobile,
            ];

            foreach ($form->fields as $field) {
                $value = $entry->getFieldValue($field->name);
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $row[] = $value;
            }

            $entryMeta = $entry->meta ?? [];
            foreach ($metaKeys as $key) {
                $row[] = $entryMeta[$key] ?? '';
            }

            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export form entries to CSV with custom format (for API usage)
     */
    public function exportFormEntriesForApi(?int $formId = null): StreamedResponse
    {
        $entries  = $this->entryRepository->getValidEntriesForExport($formId);
        $filename = 'form-entries-' . date('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(function () use ($entries) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'ID',
                'Form',
                'First Name',
                'Last Name',
                'Email',
                'Mobile',
                'User',
                'Form Data',
                'Meta',
                'Submitted At',
                'IP Address',
            ]);

            // Add data rows
            foreach ($entries as $entry) {
                fputcsv($handle, [
                    $entry->id,
                    $entry->form->name,
                    $entry->first_name,
                    $entry->last_name,
                    $entry->email,
                    $entry->mobile,
                    $entry->user ? $entry->user->name : 'Guest',
                    json_encode($entry->data),
                    json_encode($entry->meta ?? []),
                    $entry->submitted_at->format('Y-m-d H:i:s'),
                    $entry->ip_address,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
