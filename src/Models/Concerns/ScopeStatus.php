<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models\Concerns;

use Appsolutely\AIO\Enums\Status;
use Illuminate\Database\Eloquent\Builder;

trait ScopeStatus
{
    public function scopeStatus(Builder $query, mixed $value = null, ?string $operator = null): Builder
    {
        $value    = $value ?? Status::ACTIVE;
        $operator = $operator ?? '=';

        return $query->where('status', $operator, $value);
    }
}
