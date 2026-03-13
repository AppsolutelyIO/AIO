<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum OrderShipmentStatus: string
{
    case Pending   = 'pending';
    case Shipped   = 'shipped';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Shipped   => 'Shipped',
            self::Delivered => 'Delivered',
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
