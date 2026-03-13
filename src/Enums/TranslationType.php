<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum TranslationType: string
{
    case Php      = 'php';
    case Blade    = 'blade';
    case Variable = 'variable';

    public function label(): string
    {
        return match ($this) {
            self::Php      => 'PHP',
            self::Blade    => 'Blade',
            self::Variable => 'Variable',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Php->value      => self::Php->label(),
            self::Blade->value    => self::Blade->label(),
            self::Variable->value => self::Variable->label(),
        ];
    }
}
