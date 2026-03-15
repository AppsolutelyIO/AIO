<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Forms\Models;

use Appsolutely\AIO\Contracts\LazyRenderable;
use Appsolutely\AIO\Models\Model;
use Appsolutely\AIO\Traits\LazyWidget;
use Appsolutely\AIO\Widgets\Form;

abstract class ModelForm extends Form implements LazyRenderable
{
    use LazyWidget;

    protected ?int $id = null;

    protected ?Model $model = null;

    protected array $relationships = [];

    public function __construct(?int $id = null)
    {
        parent::__construct();
        $this->id = $id;
        $this->initializeModel();
    }

    /**
     * Initialize the model instance
     */
    abstract protected function initializeModel(): void;

    /**
     * Define relationships that should be synced
     */
    protected function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Handle form submission with relationship support
     */
    public function handle(array $input)
    {
        $id = $this->payload['id'] ?? null;

        try {
            if ($id) {
                $this->updateModel((int) $id, $input);

                return $this->response()->success(__t('Updated successfully'))->refresh();
            }

            $this->createModel($input);

            return $this->response()->success(__t('Created successfully'))->refresh();
        } catch (\Exception $e) {
            log_error('ModelForm handle error: ' . $e->getMessage(), [
                'model' => get_class($this->model),
                'id'    => $id,
                'input' => $input,
            ]);

            return $this->response()->error(__t('Operation failed: ') . $e->getMessage());
        }
    }

    /**
     * Create a new model with relationships
     */
    protected function createModel(array $input): void
    {
        $relationships = $this->extractRelationships($input);
        $modelData     = $this->extractModelData($input);

        $this->model->fill($modelData);
        $this->model->save();

        $this->syncRelationships($relationships);
    }

    /**
     * Update existing model with relationships
     */
    protected function updateModel(int $id, array $input): void
    {
        $model         = $this->model->findOrFail($id);
        $relationships = $this->extractRelationships($input);
        $modelData     = $this->extractModelData($input);

        $model->fill($modelData);
        $model->save();

        $this->syncRelationships($relationships, $model);
    }

    /**
     * Extract relationship data from input
     */
    protected function extractRelationships(array $input): array
    {
        $relationships      = [];
        $relationshipFields = $this->getRelationships();

        foreach ($relationshipFields as $field) {
            if (isset($input[$field])) {
                $relationships[$field] = $input[$field];
                unset($input[$field]);
            }
        }

        return $relationships;
    }

    /**
     * Extract model data from input (excluding relationships)
     */
    protected function extractModelData(array $input): array
    {
        $relationshipFields = $this->getRelationships();

        foreach ($relationshipFields as $field) {
            unset($input[$field]);
        }

        return $input;
    }

    /**
     * Sync relationships for the model
     */
    protected function syncRelationships(array $relationships, ?Model $model = null): void
    {
        $targetModel = $model ?? $this->model;

        foreach ($relationships as $relation => $values) {
            if (method_exists($targetModel, $relation)) {
                $targetModel->$relation()->sync($values);
            }
        }
    }

    /**
     * Build the form with relationship data
     */
    public function form(): void
    {
        $id = $this->payload['id'] ?? null;

        if ($id && $this->model) {
            $this->fillModelData((int) $id);
        }
    }

    /**
     * Fill form with model data including relationships
     */
    protected function fillModelData(int $id): void
    {
        $model = $this->model->with($this->getRelationships())->find($id);

        if ($model) {
            $this->model = $model;
            $this->fill($model->toArray());
        }
    }
}
