<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum BuildStatus: string
{
    case Pending   = 'pending';
    case Building  = 'building';
    case Success   = 'success';
    case Failed    = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'Pending',
            self::Building => 'Building',
            self::Success  => 'Success',
            self::Failed   => 'Failed',
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
