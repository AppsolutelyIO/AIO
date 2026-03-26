<?php

declare(strict_types=1);

use Carbon\Carbon;

if (! function_exists('timezone_convert')) {
    function timezone_convert($time, $fromTimezone, $toTimezone): Carbon
    {
        return Carbon::parse($time, $fromTimezone)
            ->setTimezone($toTimezone);
    }
}

if (! function_exists('utc_to_local_timezone')) {
    function utc_to_local_timezone($time, ?string $format = null): string
    {
        $format = $format ?? app_time_format();

        return timezone_convert($time, config('app.timezone'), app_local_timezone())
            ->format($format);
    }
}

if (! function_exists('utc_to_app_timezone')) {
    /** @deprecated Use utc_to_local_timezone() instead */
    function utc_to_app_timezone($time, ?string $format = null): string
    {
        return utc_to_local_timezone($time, $format);
    }
}

if (! function_exists('local_timezone_to_utc')) {
    function local_timezone_to_utc($time): Carbon
    {
        $standardTime = Carbon::createFromFormat(app_time_format(), $time, app_local_timezone());

        return $standardTime->copy()->setTimezone(config('app.timezone'));
    }
}

if (! function_exists('app_timezone_to_utc')) {
    /** @deprecated Use local_timezone_to_utc() instead */
    function app_timezone_to_utc($time): Carbon
    {
        return local_timezone_to_utc($time);
    }
}

if (! function_exists('user_timezone')) {
    function user_timezone(): ?string
    {
        if (auth()->check() && ! empty(auth()->user()->timezone)) {
            return auth()->user()->timezone;
        }

        return app_local_timezone();
    }
}

if (! function_exists('user_time_format')) {
    function user_time_format(): ?string
    {
        if (auth()->check() && ! empty(auth()->user()->time_format)) {
            return auth()->user()->time_format;
        }

        return app_time_format();
    }
}

if (! function_exists('utc_to_user_timezone')) {
    function utc_to_user_timezone($time, ?string $format = null): string
    {
        $format = $format ?? user_time_format();

        return timezone_convert($time, config('app.timezone'), user_timezone())
            ->format($format);
    }
}

if (! function_exists('user_timezone_to_utc')) {
    function user_timezone_to_utc($time): Carbon
    {
        $standardTime = Carbon::createFromFormat(user_time_format(), $time, user_timezone());

        return $standardTime->copy()->setTimezone(config('app.timezone'));
    }
}

if (! function_exists('parse_datetime_to_utc')) {
    /**
     * Parse a datetime string (with or without timezone offset) and convert to UTC Carbon.
     *
     * Designed for frontend-submitted ISO 8601 strings like "2026-03-26T14:00:00+08:00".
     * Laravel's datetime cast does not auto-convert to UTC when storing — this helper
     * ensures the value is always in UTC before Eloquent serializes it to the database.
     *
     * @param  string|null  $datetime  ISO 8601 or any Carbon-parseable datetime string
     * @return Carbon|null Carbon instance in UTC, or null if input is empty
     */
    function parse_datetime_to_utc(?string $datetime): ?Carbon
    {
        if ($datetime === null || $datetime === '') {
            return null;
        }

        return Carbon::parse($datetime)->utc();
    }
}
