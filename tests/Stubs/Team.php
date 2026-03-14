<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Stub Team model for package testing.
 */
class Team extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    /**
     * Find a model by its primary key or reference column, or throw an exception.
     *
     * @param  mixed  $id
     * @param  array<string>|string  $columns
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail($id, $columns = ['*']): static
    {
        if (is_numeric($id)) {
            $model = static::query()->find((int) $id, $columns);
            if ($model !== null) {
                return $model;
            }
        }

        $model = static::query()->where('reference', $id)->first($columns);
        if ($model !== null) {
            return $model;
        }

        throw (new ModelNotFoundException())->setModel(static::class, [$id]);
    }
}
