<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;

if (! function_exists('app_log')) {
    function app_log(string $message, ?array $context = [], string $type = 'info', $class = null, $function = null): void
    {
        $message = log_message($message, $class, $function);
        Log::log($type, string_concat($message), $context);
    }
}

if (! function_exists('log_error')) {
    function log_error(string $message, ?array $context = [], $class = null, $function = null): void
    {
        $message = log_message($message, $class, $function);
        Log::log('error', string_concat($message), $context);
    }
}

if (! function_exists('log_info')) {
    function log_info(string $message, ?array $context = [], $class = null, $function = null): void
    {
        $message = log_message($message, $class, $function);
        Log::log('info', string_concat($message), $context);
    }
}

if (! function_exists('log_debug')) {
    function log_debug(string $message, ?array $context = [], $class = null, $function = null): void
    {
        $message = log_message($message, $class, $function);
        Log::log('debug', string_concat($message), $context);
    }
}

if (! function_exists('log_warning')) {
    function log_warning(string $message, ?array $context = [], $class = null, $function = null): void
    {
        $message = log_message($message, $class, $function);
        Log::log('warning', string_concat($message), $context);
    }
}

if (! function_exists('log_message')) {
    function log_message($message, $class, $function): string
    {
        $string = $classAndFunction = '';
        if (! empty($class) && ! empty($function)) {
            $classAndFunction = sprintf('%s::%s', $class, $function);
        }
        $string .= $classAndFunction ? $classAndFunction . ' - ' : '';
        $string .= $message ?? '';

        return $string;
    }
}

if (! function_exists('local_debug')) {
    /**
     * Log debug message only in non-production environments.
     *
     * @param  string  $message  The debug message
     * @param  array  $context  Additional context data
     */
    function local_debug(string $message, ?array $context = []): void
    {
        if (! app()->isProduction()) {
            log_debug($message, $context);
        }
    }
}
