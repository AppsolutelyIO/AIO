<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum NotificationTriggerType: string
{
    case FormSubmission   = 'form_submission';
    case UserRegistration = 'user_registration';
    case OrderPlaced      = 'order_placed';

    public function label(): string
    {
        return match ($this) {
            self::FormSubmission   => 'Form submission',
            self::UserRegistration => 'User registration',
            self::OrderPlaced      => 'Order placed',
        };
    }

    public static function toArray(): array
    {
        return [
            self::FormSubmission->value   => self::FormSubmission->label(),
            self::UserRegistration->value => self::UserRegistration->label(),
            self::OrderPlaced->value      => self::OrderPlaced->label(),
        ];
    }
}
