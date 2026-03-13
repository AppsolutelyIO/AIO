<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models\Concerns;

use Illuminate\Support\Facades\DB;

trait HasMissingIds
{
    public static function getMissingIds(): array
    {
        return iterator_to_array(static::detectMissingIds(), false);
    }

    public static function getFirstMissingId(): ?int
    {
        foreach (static::detectMissingIds() as $id) {
            return $id;
        }

        return null;
    }

    protected static function detectMissingIds(): \Generator
    {
        $model      = new static();
        $table      = $model->getTable();
        $primaryKey = $model->getKeyName();

        $minId = DB::table($table)->min($primaryKey);
        $maxId = DB::table($table)->max($primaryKey);

        if (is_null($minId) || is_null($maxId)) {
            return;
        }

        $existingIds = DB::table($table)->pluck($primaryKey)->all();
        $existingMap = array_flip($existingIds);

        for ($i = $minId; $i <= $maxId; $i++) {
            if (! isset($existingMap[$i])) {
                yield $i;
            }
        }
    }
}
