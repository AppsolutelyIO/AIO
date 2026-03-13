<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers\Api;

use Appsolutely\AIO\Repositories\FormEntryRepository;
use Appsolutely\AIO\Services\DynamicFormExportService;

final class DynamicFormApiController extends AdminBaseApiController
{
    public function __construct(
        protected FormEntryRepository $entryRepository,
        protected DynamicFormExportService $exportService
    ) {}

    /**
     * Mark entry as spam
     */
    public function markAsSpam(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->entryRepository->markSingleAsSpam($id);

            return $this->success(null, 'Entry marked as spam successfully');
        } catch (\Exception $e) {
            return $this->failServer('Failed to mark entry as spam');
        }
    }

    /**
     * Mark entry as not spam
     */
    public function markAsNotSpam(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $this->entryRepository->markSingleAsNotSpam($id);

            return $this->success(null, 'Entry marked as valid successfully');
        } catch (\Exception $e) {
            return $this->failServer('Failed to mark entry as valid');
        }
    }

    /**
     * Export form entries as CSV
     */
    public function exportCsv(?int $formId = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return $this->exportService->exportFormEntriesForApi($formId);
    }
}
