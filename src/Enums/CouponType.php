<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum CouponType: string
{
    case FixedAmount = 'fixed_amount';
    case Percentage  = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::FixedAmount => 'Fixed Amount',
            self::Percentage  => 'Percentage',
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
