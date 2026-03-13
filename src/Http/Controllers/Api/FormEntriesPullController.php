<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Http\Controllers\Api;

use Appsolutely\AIO\Http\Requests\PullFormEntriesRequest;
use Appsolutely\AIO\Services\Contracts\FormEntriesPullServiceInterface;
use Illuminate\Http\JsonResponse;

final class FormEntriesPullController extends BaseApiController
{
    public function __construct(
        protected FormEntriesPullServiceInterface $pullService
    ) {}

    /**
     * Pull form entries (requires form_slug; optional filters).
     * Auth: Bearer token or query param "token" must match the form's api_access_token.
     */
    public function __invoke(PullFormEntriesRequest $request): JsonResponse
    {
        $token   = api_bearer_or_query_token($request);
        $filters = $this->buildFiltersFromRequest($request);

        [$ok, $statusOrPaginator, $_] = $this->pullService->pullEntries(
            $request->validated('form_slug'),
            $token,
            $filters
        );

        if (! $ok) {
            if ($statusOrPaginator === 204) {
                return response()->noContent();
            }

            $message = match ($statusOrPaginator) {
                404     => 'Form not found',
                401     => 'Invalid or missing token.',
                default => 'Unauthorized',
            };

            return $statusOrPaginator === 404
                ? $this->failNotFound($message)
                : $this->failAuth($message);
        }

        return $this->successWithPagination($statusOrPaginator);
    }

    /**
     * @return array{form_slug: string, from_date?: string, to_date?: string, entry_id_from?: int, entry_id_to?: int, page?: int, per_page?: int}
     */
    private function buildFiltersFromRequest(PullFormEntriesRequest $request): array
    {
        $filters = ['form_slug' => $request->validated('form_slug')];

        if ($request->filled('from_date')) {
            $filters['from_date'] = $request->validated('from_date');
        }
        if ($request->filled('to_date')) {
            $filters['to_date'] = $request->validated('to_date');
        }
        if ($request->filled('entry_id_from')) {
            $filters['entry_id_from'] = (int) $request->validated('entry_id_from');
        }
        if ($request->filled('entry_id_to')) {
            $filters['entry_id_to'] = (int) $request->validated('entry_id_to');
        }
        if ($request->filled('page')) {
            $filters['page'] = (int) $request->validated('page');
        }
        if ($request->filled('per_page')) {
            $filters['per_page'] = (int) $request->validated('per_page');
        }

        return $filters;
    }
}
