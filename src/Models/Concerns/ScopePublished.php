<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait ScopePublished
{
    protected static function bootScopePublished(): void
    {
        static::saving(function ($model) {
            if (empty($model->published_at)) {
                $model->published_at = now();
            }
        });
    }

    public function scopePublished(Builder $query, ?Carbon $datetime = null): void
    {
        $datetime = $datetime ?? now();

        // Published logic: published_at must be set and <= datetime
        $query->where('published_at', '<=', $datetime);

        // Expired logic: only apply if the model has expired_at column
        if ($this->hasExpiredAtColumn($query)) {
            $query->where(function ($q) use ($datetime) {
                $q->where('expired_at', '>', $datetime)
                    ->orWhereNull('expired_at');
            });
        }
    }

    /**
     * Check if the model has an expired_at column
     */
    private function hasExpiredAtColumn(Builder $query): bool
    {
        $model = $query->getModel();
        $table = $model->getTable();

        return Schema::hasColumn($table, 'expired_at');
    }
}
