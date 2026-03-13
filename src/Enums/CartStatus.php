<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum CartStatus: string
{
    case Active      = 'active';
    case Converted   = 'converted';
    case Abandoned   = 'abandoned';

    public function label(): string
    {
        return match ($this) {
            self::Active    => 'Active',
            self::Converted => 'Converted',
            self::Abandoned => 'Abandoned',
        };
    }

    public static function toArray(): array
    {
        $arr = [];
        foreach (self::cases() as $case) {
            $arr[$case->value] = $case->label();
        }

        return $arr;
    }
}
