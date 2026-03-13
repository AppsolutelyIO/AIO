<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum Status: int
{
    case INACTIVE = 0;
    case ACTIVE   = 1;

    public static function toArray(): array
    {
        return [
            self::INACTIVE->value => 'Inactive',
            self::ACTIVE->value   => 'Active',
        ];
    }

    /**
     * Same as toArray() but with labels run through __t() for admin filters/selects.
     */
    public static function toTranslatedArray(): array
    {
        return collect(self::toArray())->map(fn (string $label) => __t($label))->all();
    }
}
