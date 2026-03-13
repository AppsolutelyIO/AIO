<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait ScopeReference
{
    public function scopeReference(Builder $query, $value = null): void
    {
        if (! empty($value)) {
            $query->where('reference', $value);
        }
    }

    protected static function bootScopeReference(): void
    {
        static::creating(function ($model) {
            if (empty($model->reference)) {
                $model->reference = static::generateReference($model);
            }
        });
    }

    protected static function generateReference($model): string
    {
        $baseReference = static::getBaseReference($model);
        $reference     = $baseReference;
        $counter       = 1;

        // Check if reference already exists and append number if needed
        while (static::where('reference', $reference)->exists()) {
            $reference = $baseReference . '-' . $counter;
            $counter++;
        }

        return $reference;
    }

    protected static function getBaseReference($model): string
    {
        // Try to use title field if available
        if (isset($model->title) && ! empty($model->title)) {
            return Str::slug($model->title);
        }

        // Try to use name field if available
        if (isset($model->name) && ! empty($model->name)) {
            return Str::slug($model->name);
        }

        // Try to use summary field if available
        if (isset($model->summary) && ! empty($model->summary)) {
            return Str::slug($model->summary);
        }

        // Fallback to model class name and timestamp
        $className = class_basename($model);
        $timestamp = now()->format('YmdHis');

        return Str::slug($className) . '-' . $timestamp;
    }
}
