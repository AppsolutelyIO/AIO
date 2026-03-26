<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Models\Concerns\ClearsResponseCache;
use Appsolutely\AIO\Models\Concerns\HasMissingIds;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PageBlockValue extends Model
{
    use ClearsResponseCache;
    use HasMissingIds;

    protected $fillable = [
        'id',
        'block_id',
        'theme',
        'view',
        'view_style',
        'anchor_label',
        'query_options',
        'display_options',
        'scripts',
        'styles',
        'template',
        'published_at',
        'expired_at',
    ];

    protected $casts = [
        'query_options'   => 'array',
        'display_options' => 'array',
        'scripts'         => 'array',
        'styles'          => 'array',
        'published_at'    => 'datetime',
        'expired_at'      => 'datetime',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(PageBlock::class, 'block_id');
    }
}
