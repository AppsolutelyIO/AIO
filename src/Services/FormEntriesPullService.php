<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\FormEntry;
use Appsolutely\AIO\Repositories\FormEntryRepository;
use Appsolutely\AIO\Repositories\FormRepository;
use Appsolutely\AIO\Services\Contracts\FormEntriesPullServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;

final class FormEntriesPullService implements FormEntriesPullServiceInterface
{
    public function __construct(
        protected FormRepository $formRepository,
        protected FormEntryRepository $entryRepository
    ) {}

    /**
     * @param  array{form_slug: string, from_date?: string, to_date?: string, entry_id_from?: int, entry_id_to?: int, page?: int, per_page?: int}  $filters
     * @return array{0: bool, 1: int|LengthAwarePaginator, 2: null}
     */
    public function pullEntries(string $formSlug, ?string $token, array $filters): array
    {
        $form = $this->formRepository->findBySlugForApi($formSlug);

        if (! $form) {
            return [false, 404, null];
        }

        if (empty($form->api_access_token) || $token === null) {
            return [false, 204, null];
        }

        if ((string) $form->api_access_token !== (string) $token) {
            return [false, 401, null];
        }

        $paginator = $this->entryRepository->getEntriesByFiltersPaginated($filters);
        $items     = $paginator->getCollection()->map(fn (FormEntry $entry) => $this->entryToApiArray($entry))->values();

        $apiPaginator = new LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => $paginator->path()]
        );

        return [true, $apiPaginator, null];
    }

    /**
     * @return array<string, mixed>
     */
    private function entryToApiArray(FormEntry $entry): array
    {
        return [
            'id'           => $entry->id,
            'form_id'      => $entry->form_id,
            'submitted_at' => $entry->submitted_at?->toIso8601String(),
            'name'         => $entry->name,
            'first_name'   => $entry->first_name,
            'last_name'    => $entry->last_name,
            'email'        => $entry->email,
            'mobile'       => $entry->mobile,
            'referer'      => $entry->referer,
            'data'         => $entry->data ?? [],
            'meta'         => $entry->meta ?? [],
        ];
    }
}
