<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum Architecture: string
{
    case X86_64    = 'x86_64';
    case ARM64     = 'arm64';
    case ARMv7     = 'armv7';
    case IA32      = 'ia32';
    case Universal = 'universal';
    case Other     = 'other';

    public function label(): string
    {
        return match ($this) {
            self::X86_64    => 'x86_64 (64-bit Intel/AMD)',
            self::ARM64     => 'arm64 (Apple Silicon, ARM64)',
            self::ARMv7     => 'armv7 (32-bit ARM)',
            self::IA32      => 'ia32 (32-bit Intel/AMD)',
            self::Universal => 'universal (macOS Universal)',
            self::Other     => 'Other',
        };
    }

    public static function toArray(): array
    {
        return [
            self::X86_64->value    => self::X86_64->label(),
            self::ARM64->value     => self::ARM64->label(),
            self::ARMv7->value     => self::ARMv7->label(),
            self::IA32->value      => self::IA32->label(),
            self::Universal->value => self::Universal->label(),
            self::Other->value     => self::Other->label(),
        ];
    }
}
