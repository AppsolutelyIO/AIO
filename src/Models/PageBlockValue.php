<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Models\Concerns\ClearsResponseCache;
use Appsolutely\AIO\Models\Concerns\HasMissingIds;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $block_id
 * @property string|null $theme
 * @property string|null $view
 * @property string|null $view_style
 * @property string|null $anchor_label
 * @property array|null $query_options
 * @property array|null $display_options
 * @property array|null $scripts
 * @property array|null $styles
 * @property string|null $template
 * @property Carbon|null $published_at
 * @property Carbon|null $expired_at
 */
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
