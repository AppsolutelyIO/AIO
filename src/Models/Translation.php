<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\TranslationType;
use Appsolutely\AIO\Enums\TranslatorType;

class Translation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'locale',
        'type',
        'original_text',
        'translated_text',
        'translator',
        'call_stack',
        'used_count',
        'last_used',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type'       => TranslationType::class,
        'translator' => TranslatorType::class,
        'last_used'  => 'datetime',
        'used_count' => 'integer',
    ];
}
