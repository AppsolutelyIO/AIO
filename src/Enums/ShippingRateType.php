<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum ShippingRateType: string
{
    case FlatRate     = 'flat_rate';
    case FreeShipping = 'free_shipping';
    case WeightBased  = 'weight_based';

    public function label(): string
    {
        return match ($this) {
            self::FlatRate     => 'Flat Rate',
            self::FreeShipping => 'Free Shipping',
            self::WeightBased  => 'Weight Based',
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
