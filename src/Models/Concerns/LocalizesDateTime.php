<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models\Concerns;

use Carbon\Carbon;

trait LocalizesDateTime
{
    /**
     * Cache for datetime fields per model class
     */
    protected static array $cachedDateTimeFields = [];

    protected array $_dirty;

    public const PREFIX = '_';

    /**
     * Boot the LocalizesTimestamps trait for a model.
     *
     * @return void
     */
    public static function bootLocalizesDateTime()
    {
        static::retrieved(function ($model) {
            foreach ($model->getLocalDateTimeFields() as $field) {
                // Only override if the attribute exists
                if (array_key_exists($field, $model->attributes ?? []) && $model->getOriginal($field) instanceof Carbon) {
                    $time = $model->getRawOriginal($field);
                    $model->setAttribute(self::getPrefix() . $field, utc_to_app_timezone($time));
                }
            }
        });

        static::saving(function ($model) {
            foreach ($model->getLocalDateTimeFields() as $field) {
                $localKey = self::getPrefix() . $field;
                if (! $model->isDirty($field) && ! in_array($field, static::getStandardTimestampKeys()) && ! empty($model->attributes[$localKey])) {
                    if (! empty($model->_dirty[$localKey])) {
                        $value = $model->_dirty[$localKey];
                    } elseif (! empty($model->attributes[$localKey])) {
                        $value = $model->attributes[$localKey];
                    } else {
                        $value = Carbon::now();
                    }
                    $model->attributes[$field] = app_timezone_to_utc($value) ?? Carbon::now();
                }
                unset($model->attributes[$localKey]);
            }
        });
    }

    /**
     * Get the list of timestamp fields to convert to local time.
     * Override this property in your model as needed.
     */
    protected function getLocalDateTimeFields(): array
    {
        $modelClass = static::class;

        // Return cached result if available
        if (isset(static::$cachedDateTimeFields[$modelClass])) {
            return static::$cachedDateTimeFields[$modelClass];
        }

        $datetimeFields = [];

        // Get datetime fields from $casts
        foreach ($this->getCasts() as $field => $cast) {
            if (in_array($cast, ['datetime', 'date', 'timestamp'])) {
                $datetimeFields[] = $field;
            }
        }

        // Get all table columns once and check for standard timestamp fields
        try {
            $availableFields = \Schema::getColumnListing($this->getTable());
        } catch (\Exception $e) {
            // Fallback: use model attributes if schema fails
            $availableFields = array_keys($this->attributes ?? []);
        }

        $existingTimestampFields = array_intersect($this->getStandardTimestampKeys(), $availableFields);
        $datetimeFields          = array_merge($datetimeFields, array_diff($existingTimestampFields, $datetimeFields));

        // Cache the result
        static::$cachedDateTimeFields[$modelClass] = $datetimeFields;

        return $datetimeFields;
    }

    protected static function getStandardTimestampKeys(): array
    {
        return ['created_at', 'updated_at', 'deleted_at'];
    }

    public static function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function setDirty(array $data): static
    {
        $this->_dirty = $data;

        return $this;
    }
}
