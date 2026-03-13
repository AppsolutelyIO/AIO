<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories\Traits;

use Appsolutely\AIO\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait Reference
{
    public function reference(string $reference): Builder
    {
        return $this->model->reference($reference);
    }

    public function findByReference(string $reference): Model
    {
        return $this->model->reference($reference)->firstOrFail();
    }

    public function getByReference(string $reference): Collection
    {
        return $this->model->reference($reference)->get();
    }
}
