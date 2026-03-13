<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Enums;

enum FormFieldType: string
{
    case Text           = 'text';
    case Textarea       = 'textarea';
    case Email          = 'email';
    case Number         = 'number';
    case Select         = 'select';
    case MultipleSelect = 'multiple_select';
    case Radio          = 'radio';
    case Checkbox       = 'checkbox';
    case File           = 'file';
    case Date           = 'date';
    case Time           = 'time';
    case DateTime       = 'datetime';
    case Hidden         = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Text           => 'Text Input',
            self::Textarea       => 'Textarea',
            self::Email          => 'Email',
            self::Number         => 'Number',
            self::Select         => 'Select Dropdown',
            self::MultipleSelect => 'Multiple Select',
            self::Radio          => 'Radio Buttons',
            self::Checkbox       => 'Checkboxes',
            self::File           => 'File Upload',
            self::Date           => 'Date',
            self::Time           => 'Time',
            self::DateTime       => 'Date & Time',
            self::Hidden         => 'Hidden Field',
        };
    }

    public static function toArray(): array
    {
        return [
            self::Text->value           => self::Text->label(),
            self::Textarea->value       => self::Textarea->label(),
            self::Email->value          => self::Email->label(),
            self::Number->value         => self::Number->label(),
            self::Select->value         => self::Select->label(),
            self::MultipleSelect->value => self::MultipleSelect->label(),
            self::Radio->value          => self::Radio->label(),
            self::Checkbox->value       => self::Checkbox->label(),
            self::File->value           => self::File->label(),
            self::Date->value           => self::Date->label(),
            self::Time->value           => self::Time->label(),
            self::DateTime->value       => self::DateTime->label(),
            self::Hidden->value         => self::Hidden->label(),
        ];
    }

    /**
     * Check if field type supports options
     */
    public function supportsOptions(): bool
    {
        return in_array($this, [
            self::Select,
            self::MultipleSelect,
            self::Radio,
            self::Checkbox,
        ]);
    }

    /**
     * Check if field type supports multiple values
     */
    public function supportsMultipleValues(): bool
    {
        return in_array($this, [
            self::MultipleSelect,
            self::Checkbox,
        ]);
    }
}
