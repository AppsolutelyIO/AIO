<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum TranslatorType: string
{
    case Google    = 'Google';
    case DeepSeek  = 'DeepSeek';
    case OpenAI    = 'OpenAI';
    case Manual    = 'Manual';

    public function label(): string
    {
        return match ($this) {
            self::Google   => 'Google',
            self::DeepSeek => 'DeepSeek',
            self::OpenAI   => 'OpenAI',
            self::Manual   => 'Manual',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Google->value   => self::Google->label(),
            self::DeepSeek->value => self::DeepSeek->label(),
            self::OpenAI->value   => self::OpenAI->label(),
            self::Manual->value   => self::Manual->label(),
        ];
    }
}
