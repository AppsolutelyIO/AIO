<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum PageType: string
{
    case Nested     = 'nested';
    case Root       = 'root';

    public function toArray(): string
    {
        return match ($this) {
            self::Nested => 'Nested',
            self::Root   => 'Root',
        };
    }
}
