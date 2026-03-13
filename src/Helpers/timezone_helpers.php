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

if (! function_exists('utc_to_app_timezone')) {
    function utc_to_app_timezone($time, ?string $format = null): string
    {
        $format = $format ?? app_time_format();

        return timezone_convert($time, config('app.timezone'), app_local_timezone())
            ->format($format);
    }
}

if (! function_exists('app_timezone_to_utc')) {
    function app_timezone_to_utc($time): Carbon
    {
        $standardTime = Carbon::createFromFormat(app_time_format(), $time, app_local_timezone());

        return $standardTime->copy()->setTimezone(config('app.timezone'));
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
