<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum ProductType: string
{
    case Physical            = 'PHYSICAL';
    case AutoVirtual         = 'AUTO_VIRTUAL';
    case ManualVirtual       = 'MANUAL_VIRTUAL';

    public function label(): string
    {
        return match ($this) {
            self::Physical      => 'Physical product',
            self::AutoVirtual   => 'Auto-deliverable virtual product',
            self::ManualVirtual => 'Manual-deliverable virtual product',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Physical->value      => self::Physical->label(),
            self::AutoVirtual->value   => self::AutoVirtual->label(),
            self::ManualVirtual->value => self::ManualVirtual->label(),
        ];
    }
}
