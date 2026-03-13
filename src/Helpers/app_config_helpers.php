<?php

declare(strict_types=1);

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

if (! function_exists('appsolutely')) {
    /**
     * Get the Appsolutely prefix for database or cache keys.
     *
     * @param  string|null  $prefix  to append
     * @return string The generated prefix
     */
    function appsolutely(?string $prefix = null): string
    {
        $result = config('appsolutely.prefix');

        if ($prefix !== null) {
            $result = "app_{$prefix}";
            config(['appsolutely.prefix' => $result]);
        }

        return $result;
    }
}

if (! function_exists('string_concat')) {
    /**
     * Concatenate a string with an optional prefix.
     *
     * @param  string  $string  The string to concatenate
     * @param  string|null  $prefix  Optional prefix (defaults to appsolutely prefix)
     * @return string The concatenated string
     */
    function string_concat(string $string, ?string $prefix = null): string
    {
        return ($prefix ?? appsolutely()) . ' - ' . $string;
    }
}

if (! function_exists('app_local_timezone')) {
    function app_local_timezone(): string
    {
        return config('appsolutely.local_timezone');
    }
}

if (! function_exists('app_time_format')) {
    function app_time_format(): string
    {
        return config('appsolutely.time_format');
    }
}

if (! function_exists('app_currency_symbol')) {
    function app_currency_symbol(): string
    {
        return config('appsolutely.currency.symbol', '$');
    }
}

/**
 * ISO 4217 currency subunit factors.
 * Maps currency codes to the number of smallest units per major unit.
 * Only non-100 currencies are listed; unlisted currencies default to 100.
 */
if (! defined('CURRENCY_ZERO_DECIMAL')) {
    define('CURRENCY_ZERO_DECIMAL', [
        'BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ]);
}

if (! defined('CURRENCY_THREE_DECIMAL')) {
    define('CURRENCY_THREE_DECIMAL', [
        'BHD', 'IQD', 'JOD', 'KWD', 'LYD', 'OMR', 'TND',
    ]);
}

if (! function_exists('currency_subunit_factor')) {
    /**
     * Get the subunit factor for a currency (number of smallest units per major unit).
     *
     * @param  string|null  $currency  ISO 4217 currency code (e.g. 'USD', 'JPY'). Defaults to app currency.
     * @return int 1 for zero-decimal, 100 for two-decimal, 1000 for three-decimal currencies
     */
    function currency_subunit_factor(?string $currency = null): int
    {
        $currency = strtoupper($currency ?? config('appsolutely.currency.code', 'USD'));

        if (in_array($currency, CURRENCY_ZERO_DECIMAL, true)) {
            return 1;
        }

        if (in_array($currency, CURRENCY_THREE_DECIMAL, true)) {
            return 1000;
        }

        return 100;
    }
}

/**
 * Number of cents (smallest currency unit) per one major currency unit.
 * Uses the application's configured currency. For currency-specific conversions,
 * use currency_subunit_factor() directly with a currency code.
 */
if (! defined('CENTS_PER_UNIT')) {
    define('CENTS_PER_UNIT', 100);
}

/**
 * Basis points divisor for percentage calculations.
 * A percentage stored as basis points (e.g. 1500 = 15%) is divided by this
 * value when applied to a cents amount: amount * basisPoints / BASIS_POINTS_DIVISOR.
 */
if (! defined('BASIS_POINTS_DIVISOR')) {
    define('BASIS_POINTS_DIVISOR', CENTS_PER_UNIT * CENTS_PER_UNIT);
}

if (! function_exists('format_cents')) {
    /**
     * Format a subunit amount as a decimal string for display.
     *
     * @param  int|null  $cents  Amount in smallest currency unit (e.g. 9990 = 99.90 for USD)
     * @param  int|null  $decimals  Number of decimal places. Auto-detected from currency if null.
     * @param  string|null  $currency  ISO 4217 currency code. Defaults to app currency.
     * @return string Formatted decimal string (e.g. "99.90")
     */
    function format_cents(?int $cents, ?int $decimals = null, ?string $currency = null): string
    {
        $factor = currency_subunit_factor($currency);

        if ($decimals === null) {
            $decimals = match ($factor) {
                1       => 0,
                1000    => 3,
                default => 2,
            };
        }

        if ($cents === null) {
            return $decimals > 0 ? '0.' . str_repeat('0', $decimals) : '0';
        }

        return number_format($cents / $factor, $decimals);
    }
}

if (! function_exists('subunit_to_display')) {
    /**
     * Closure that converts integer subunit amount to decimal for display.
     *
     * @param  string|null  $currency  ISO 4217 currency code. Defaults to app currency.
     */
    function subunit_to_display(?string $currency = null): Closure
    {
        $factor = currency_subunit_factor($currency);

        return fn ($v) => $v !== null ? $v / $factor : null;
    }
}

if (! function_exists('display_to_subunit')) {
    /**
     * Closure that converts decimal display value back to integer subunit amount for storage.
     *
     * @param  string|null  $currency  ISO 4217 currency code. Defaults to app currency.
     */
    function display_to_subunit(?string $currency = null): Closure
    {
        $factor = currency_subunit_factor($currency);

        return fn ($v) => (int) round((float) $v * $factor);
    }
}

if (! function_exists('app_theme')) {
    function app_theme(): string
    {
        return config('appsolutely.theme.name');
    }
}

if (! function_exists('supported_locales')) {
    function supported_locales(): array
    {
        return config('appsolutely.multiple_locales') ? LaravelLocalization::getSupportedLocales() : [LaravelLocalization::getDefaultLocale()];
    }
}
