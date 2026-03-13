<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum CouponStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Expired  = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active   => 'Active',
            self::Inactive => 'Inactive',
            self::Expired  => 'Expired',
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
