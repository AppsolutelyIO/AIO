<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories\Traits;

use Appsolutely\AIO\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait Status
{
    public function status(?string $value = null): Builder
    {
        return $this->model->status($value);
    }

    public function findByStatus(string $value): Model
    {
        return $this->model->status($value)->firstOrFail();
    }

    public function getByStatus(string $value): Collection
    {
        return $this->model->status($value)->get();
    }
}
