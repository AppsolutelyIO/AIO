<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum NotificationQueueStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Sent       = 'sent';
    case Failed     = 'failed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending    => __t('Pending'),
            self::Processing => __t('Processing'),
            self::Sent       => __t('Sent'),
            self::Failed     => __t('Failed'),
            self::Cancelled  => __t('Cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'warning',
            self::Processing => 'info',
            self::Sent       => 'success',
            self::Failed     => 'danger',
            self::Cancelled  => 'secondary',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function toColorArray(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->color()])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
