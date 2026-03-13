<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Paid       = 'paid';
    case Shipped    = 'shipped';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Paid      => 'Paid',
            self::Shipped   => 'Shipped',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
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
