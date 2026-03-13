<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\FormField;
use Illuminate\Database\Eloquent\Collection;

final class FormFieldRepository extends BaseRepository
{
    public function model(): string
    {
        return FormField::class;
    }

    /**
     * Get fields for a specific form ordered by sort
     */
    public function getFieldsByForm(int $formId): Collection
    {
        return $this->model->newQuery()
            ->where('form_id', $formId)
            ->orderBy('sort')
            ->get();
    }

    /**
     * Get required fields for a form
     */
    public function getRequiredFields(int $formId): Collection
    {
        return $this->model->newQuery()
            ->where('form_id', $formId)
            ->where('required', true)
            ->orderBy('sort')
            ->get();
    }

    /**
     * Get field by form and name
     */
    public function getFieldByName(int $formId, string $name): ?FormField
    {
        return $this->model->newQuery()
            ->where('form_id', $formId)
            ->where('name', $name)
            ->first();
    }

    /**
     * Update field sort orders
     */
    public function updateSortOrders(array $fieldSorts): void
    {
        foreach ($fieldSorts as $fieldId => $sort) {
            $this->model->newQuery()
                ->where('id', $fieldId)
                ->update(['sort' => $sort]);
        }
    }

    /**
     * Get fields with options (select, radio, checkbox)
     */
    public function getFieldsWithOptions(int $formId): Collection
    {
        return $this->model->newQuery()
            ->where('form_id', $formId)
            ->whereIn('type', ['select', 'radio', 'checkbox', 'multiple_select'])
            ->orderBy('sort')
            ->get();
    }

    /**
     * Duplicate fields to another form
     */
    public function duplicateToForm(int $sourceFormId, int $targetFormId): Collection
    {
        $sourceFields     = $this->getFieldsByForm($sourceFormId);
        $duplicatedFields = collect();

        foreach ($sourceFields as $field) {
            $newField = $this->create([
                'form_id'     => $targetFormId,
                'label'       => $field->label,
                'name'        => $field->name,
                'type'        => $field->type,
                'placeholder' => $field->placeholder,
                'required'    => $field->required,
                'options'     => $field->options,
                'sort'        => $field->sort,
                'setting'     => $field->setting,
            ]);

            $duplicatedFields->push($newField);
        }

        return $duplicatedFields;
    }
}
