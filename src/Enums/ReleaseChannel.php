<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum ReleaseChannel: string
{
    case Stable  = 'stable';
    case Beta    = 'beta';
    case Dev     = 'dev';

    public function label(): string
    {
        return match ($this) {
            self::Stable => 'Stable',
            self::Beta   => 'Beta',
            self::Dev    => 'Dev',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Stable->value => self::Stable->label(),
            self::Beta->value   => self::Beta->label(),
            self::Dev->value    => self::Dev->label(),
        ];
    }
}
