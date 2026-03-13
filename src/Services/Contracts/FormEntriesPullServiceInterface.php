<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FormEntriesPullServiceInterface
{
    /**
     * Pull form entries for a form by slug (paginated), after validating API token.
     * Returns a 3-element array for list() destructuring: [ok, statusOrPaginator, _].
     * - On error: [false, status (404|403|401|204), null]. 204 = form has no token OR request sent no token (controller returns no content).
     * - On success: [true, LengthAwarePaginator (items are API-shaped arrays), null].
     *
     * @param  array{form_slug: string, from_date?: string, to_date?: string, entry_id_from?: int, entry_id_to?: int, page?: int, per_page?: int}  $filters
     * @return array{0: bool, 1: int|LengthAwarePaginator, 2: null}
     */
    public function pullEntries(string $formSlug, ?string $token, array $filters): array;
}
