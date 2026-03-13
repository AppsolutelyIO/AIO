<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface FormExportServiceInterface
{
    /**
     * Export form entries to CSV file
     *
     * @param  int  $formId  Form ID to export
     * @param  bool  $includeSpam  Whether to include spam entries
     * @param  bool  $includeMetadata  Whether to include metadata columns (IP, User Agent, etc.)
     * @return string Full path to the generated CSV file
     */
    public function exportFormEntriesToCsv(int $formId, bool $includeSpam = false, bool $includeMetadata = false): string;

    /**
     * Export form entries to Excel file
     *
     * @param  int  $formId  Form ID to export
     * @param  bool  $includeSpam  Whether to include spam entries
     * @param  bool  $includeMetadata  Whether to include metadata columns (IP, User Agent, etc.)
     * @return string Full path to the generated Excel file
     */
    public function exportFormEntriesToExcel(int $formId, bool $includeSpam = false, bool $includeMetadata = false): string;
}
