<?php

declare(strict_types=1);

use Appsolutely\AIO\Services\TranslationService;
use Illuminate\Support\Facades\Lang;

if (! function_exists('__translate')) {
    /**
     * Base translation function
     *
     * @param  string  $text  The text to translate
     * @param  array  $parameters  Parameters to replace in the translated text
     * @param  string  $type  The source type (php, blade, variable)
     * @param  string|null  $locale  The locale to translate to (default: current locale)
     * @return string The translated text
     */
    function __translate(string $text, array $parameters = [], string $type = 'php', ?string $locale = null): string
    {
        $translationService = app(TranslationService::class);

        // Get full debug backtrace
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // Collect all files in the call stack
        $callStackFiles = [];
        $basePath       = base_path() . '/';
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && isset($trace['line'])) {
                // Skip vendor files
                if (str_contains($trace['file'], '/vendor/')) {
                    continue;
                }

                // Convert to project-relative path
                $relativePath     = str_replace($basePath, '', $trace['file']);
                $callStackFiles[] = $relativePath . ':' . $trace['line'];
            }
        }

        // Create a string with each file on its own line
        $callStack = implode("\r\n", $callStackFiles);

        // If type is auto-detected, determine based on source location
        if ($type === 'auto') {
            $type = 'php';
            // Check all files in the call stack for blade files
            foreach ($callStackFiles as $file) {
                // Compiled blade views are stored in storage/framework/views
                if (str_contains($file, 'storage/framework/views') || str_contains($file, '.blade.php')) {
                    $type = 'blade';
                    break;
                }
            }
        }

        $translatedText = $translationService->translate($text, $locale, $type, $callStack);

        // Replace parameters if provided
        if (! empty($parameters)) {
            foreach ($parameters as $key => $value) {
                $translatedText = str_replace(':' . $key, (string) $value, $translatedText);
                $translatedText = str_replace('%s', (string) $value, $translatedText);
            }
        }

        return $translatedText;
    }
}

if (! function_exists('__t')) {
    /**
     * Translate a string. Tries Laravel lang files first, then falls back to TranslationService (DB).
     *
     * @param  string  $text  The text or translation key (e.g. 'global.hero.learn_more' or 'admin.title')
     * @param  array  $parameters  Parameters to replace in the translated text (e.g. [':name' => 'John'])
     * @param  string|null  $locale  The locale to translate to (default: current locale)
     * @return string The translated text
     */
    function __t(string $text, array $parameters = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        // Try Laravel's lang files first (lang/{locale}/*.php)
        if (Lang::has($text, $locale)) {
            return __($text, $parameters, $locale);
        }

        return __translate($text, $parameters, 'auto', $locale);
    }
}

if (! function_exists('__tv')) {
    /**
     * Translate a string from a variable using the TranslationService
     * Use this for dynamic content like data from variables or database fields
     *
     * @param  string  $text  The text to translate
     * @param  array  $parameters  Parameters to replace in the translated text
     * @param  string|null  $locale  The locale to translate to (default: current locale)
     * @return string The translated text
     */
    function __tv(string $text, array $parameters = [], ?string $locale = null): string
    {
        return __translate($text, $parameters, 'variable', $locale);
    }
}

if (! function_exists('translate_enum_options')) {
    /**
     * Translate labels in an options array (e.g. from Enum::toArray()).
     * Preserves keys and passes each label through __t().
     *
     * @param  array<int|string, string>  $options  [value => label, ...]
     * @return array<int|string, string> [value => translated label, ...]
     */
    function translate_enum_options(array $options): array
    {
        return collect($options)->mapWithKeys(fn (string $label, int|string $value) => [$value => __t($label)])->all();
    }
}
