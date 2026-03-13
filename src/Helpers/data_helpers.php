<?php

declare(strict_types=1);

use Appsolutely\AIO\Models\GeneralPage;

if (! function_exists('associative_array')) {
    function associative_array(array $items): array
    {
        return collect($items)
            ->unique()
            ->mapWithKeys(fn ($item) => [$item => $item])
            ->toArray();
    }
}

if (! function_exists('enum_options')) {
    /**
     * Get options array for select fields from an enum.
     *
     * @param  string  $enumClass  The enum class name
     * @param  string  $labelMethod  The method to use for labels (default: 'toArray', fallback: 'name')
     * @return array Array with enum values as keys and labels as values
     */
    function enum_options(string $enumClass, string $labelMethod = 'toArray'): array
    {
        if (! class_exists($enumClass) || ! enum_exists($enumClass)) {
            return [];
        }

        return collect($enumClass::cases())->mapWithKeys(function ($case) use ($labelMethod) {
            // Try to use the specified label method if it exists
            if (method_exists($case, $labelMethod)) {
                return [$case->value => $case->$labelMethod()];
            }

            // Fallback to enum name if the method doesn't exist
            return [$case->value => $case->name];
        })->toArray();
    }
}

if (! function_exists('setting_get')) {
    /**
     * Normalize content setting (array or JSON string) and optionally get a key.
     * Use when reading from model->setting which may be cast to array or raw JSON string.
     *
     * @param  mixed  $setting  Raw setting (array, JSON string, or null)
     * @param  string|null  $key  Optional key to get from the setting array
     * @param  mixed  $default  Default when key is missing or setting is invalid (used only when $key is not null)
     * @return mixed When $key is null: normalized array or null. When $key is set: the key value or $default
     */
    function setting_get(mixed $setting, ?string $key = null, mixed $default = null): mixed
    {
        if ($setting === null) {
            return $key === null ? null : $default;
        }
        if (is_string($setting) && $setting !== '') {
            $decoded = json_decode($setting, true);
            $setting = is_array($decoded) ? $decoded : null;
        }
        if (! is_array($setting)) {
            return $key === null ? null : $default;
        }
        if ($key === null) {
            return $setting;
        }

        return $setting[$key] ?? $default;
    }
}

if (! function_exists('get_property')) {
    function get_property($target, $key, $default = null)
    {
        if (is_array($target)) {
            return $target[$key] ?? $default;
        }
        if (is_object($target)) {
            return $target->$key ?? $default;
        }

        return $default;
    }
}

if (! function_exists('page_meta')) {
    function page_meta(?GeneralPage $page, $key): string
    {
        if ($page === null) {
            return '';
        }

        $value = get_property($page, $key);
        if (! empty($value)) {
            return $value;
        }

        $value = get_property($page->getContent(), $key);
        if (! empty($value)) {
            return $value;
        }

        return $page->toArray()[$key] ?? '';
    }
}
