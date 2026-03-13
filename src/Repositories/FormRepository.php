<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Form;
use Illuminate\Database\Eloquent\Collection;

final class FormRepository extends BaseRepository
{
    public function model(): string
    {
        return Form::class;
    }

    /**
     * Find form by slug (active forms only)
     */
    public function findBySlug(string $slug): ?Form
    {
        return $this->model->newQuery()
            ->with(['fields'])
            ->where('slug', $slug)
            ->status()
            ->first();
    }

    /**
     * Find form by slug for API (any status) — used to distinguish 404 vs 401
     */
    public function findBySlugForApi(string $slug): ?Form
    {
        return $this->model->newQuery()
            ->with(['fields'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get all active forms with their fields
     */
    public function getActiveFormsWithFields(): Collection
    {
        return $this->model->newQuery()
            ->with(['fields' => function ($query) {
                $query->orderBy('sort');
            }])
            ->status()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get form with fields and entries count
     */
    public function getFormWithStats(int $id): ?Form
    {
        return $this->model->newQuery()
            ->withCount(['entries', 'validEntries'])
            ->with(['fields' => function ($query) {
                $query->orderBy('sort');
            }])
            ->find($id);
    }

    /**
     * Get forms with entry statistics
     */
    public function getFormsWithStats(): Collection
    {
        return $this->model->newQuery()
            ->withCount(['entries', 'validEntries'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create form with fields
     */
    public function createWithFields(array $formData, array $fieldsData): Form
    {
        $form = $this->create($formData);

        foreach ($fieldsData as $fieldData) {
            $form->fields()->create($fieldData);
        }

        return $form->load('fields');
    }

    /**
     * Update form and sync fields
     */
    public function updateWithFields(int $id, array $formData, array $fieldsData): Form
    {
        $form = $this->update($formData, $id);

        // Delete existing fields not in the new data
        $existingFieldIds = collect($fieldsData)
            ->pluck('id')
            ->filter()
            ->toArray();

        $form->fields()
            ->whereNotIn('id', $existingFieldIds)
            ->delete();

        // Update or create fields
        foreach ($fieldsData as $fieldData) {
            if (isset($fieldData['id'])) {
                $form->fields()
                    ->where('id', $fieldData['id'])
                    ->update($fieldData);
            } else {
                $form->fields()->create($fieldData);
            }
        }

        return $form->load('fields');
    }

    /**
     * Get form options for select dropdown
     */
    public function getFormOptions(): array
    {
        return $this->model->pluck('name', 'id')->toArray();
    }

    /**
     * Count forms by status
     */
    public function countByStatus(int $status): int
    {
        return $this->model->where('status', $status)->count();
    }
}
