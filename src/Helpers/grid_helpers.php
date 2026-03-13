<?php

declare(strict_types=1);

use Appsolutely\AIO\Helpers\FileHelper;

if (! function_exists('extract_values')) {
    function extract_values($columnKey = 'id'): Closure
    {
        return function ($v) use ($columnKey) {
            if (! $v) {
                return [];
            }

            return array_column($v, $columnKey);
        };
    }
}

if (! function_exists('column_value')) {
    function column_value($key = '', $searches = ';', $replaces = '<br/>'): \Closure
    {
        return function ($data) use ($key, $searches, $replaces) {
            if (empty($data[$key])) {
                return '';
            }
            if (empty($searches) || empty($replaces)) {
                return $data[$key];
            }

            return str_replace($searches, $replaces, $data[$key]);
        };
    }
}

if (! function_exists('column_value_simple')) {
    function column_value_simple($column, $key = null): \Closure
    {
        return function ($data) use ($key, $column) {
            $data = $data[$key] ?? $data;

            return empty($column) ? '' : implode('-', array_column($data, $column));
        };
    }
}

if (! function_exists('column_count')) {
    function column_count(): \Closure
    {
        return function ($data) {
            return is_countable($data) ? count($data) : 0;
        };
    }
}

if (! function_exists('column_time_format')) {
    function column_time_format(): \Closure
    {
        return function ($datetime) {
            if (! $datetime) {
                return '—';
            }

            return utc_to_app_timezone($datetime);
        };
    }
}

if (! function_exists('column_file_size')) {
    function column_file_size(): \Closure
    {
        return function ($size) {
            return FileHelper::formatSize($size);
        };
    }
}

if (! function_exists('children_attributes')) {
    function children_attributes(): array
    {
        return ['data-column' => 'children'];
    }
}
