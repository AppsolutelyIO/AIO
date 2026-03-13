<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum Platform: string
{
    case Windows = 'windows';
    case Darwin  = 'darwin';
    case Linux   = 'linux';
    case iOS     = 'ios';
    case Android = 'android';
    case Other   = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Windows => 'Windows',
            self::Darwin  => 'Darwin',
            self::Linux   => 'Linux',
            self::iOS     => 'iOS',
            self::Android => 'Android',
            self::Other   => 'Other',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Windows->value => self::Windows->label(),
            self::Darwin->value  => self::Darwin->label(),
            self::Linux->value   => self::Linux->label(),
            self::iOS->value     => self::iOS->label(),
            self::Android->value => self::Android->label(),
            self::Other->value   => self::Other->label(),
        ];
    }
}
