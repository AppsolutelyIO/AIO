<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum InventoryMovementType: string
{
    case Purchase     = 'purchase';
    case Sale         = 'sale';
    case Return       = 'return';
    case Adjustment   = 'adjustment';
    case Reservation  = 'reservation';
    case Cancellation = 'cancellation';

    public function label(): string
    {
        return match ($this) {
            self::Purchase     => 'Purchase',
            self::Sale         => 'Sale',
            self::Return       => 'Return',
            self::Adjustment   => 'Adjustment',
            self::Reservation  => 'Reservation',
            self::Cancellation => 'Cancellation',
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
