<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum FormEntrySpamStatus: int
{
    case Valid = 0;
    case Spam  = 1;

    public function label(): string
    {
        return match ($this) {
            self::Valid => 'Valid',
            self::Spam  => 'Spam',
        };
    }

    public function isSpam(): bool
    {
        return $this === self::Spam;
    }

    /**
     * Return 'Yes' for Spam, 'No' for Valid (e.g. for exports).
     */
    public function toYesNo(): string
    {
        return $this->isSpam() ? 'Yes' : 'No';
    }

    /**
     * Return 'Yes' or 'No' from a value that may be this enum or a raw int/bool.
     */
    public static function toYesNoFrom(mixed $value): string
    {
        return $value instanceof self ? $value->toYesNo() : ((bool) $value ? 'Yes' : 'No');
    }

    public static function toArray(): array
    {
        return [
            self::Valid->value => self::Valid->label(),
            self::Spam->value  => self::Spam->label(),
        ];
    }
}
