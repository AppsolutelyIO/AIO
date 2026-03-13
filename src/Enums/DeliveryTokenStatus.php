<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum DeliveryTokenStatus: string
{
    case Pending   = 'pending';
    case Delivered = 'delivered';
    case Failed    = 'failed';
    case Expired   = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Pending',
            self::Delivered => 'Delivered',
            self::Failed    => 'Failed',
            self::Expired   => 'Expired',
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
