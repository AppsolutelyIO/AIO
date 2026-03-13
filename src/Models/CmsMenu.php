<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\MenuTarget;
use Appsolutely\AIO\Enums\MenuType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\ClearsResponseCache;
use Appsolutely\AIO\Models\Concerns\ScopePublished;
use Appsolutely\AIO\Models\Concerns\ScopeReference;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Appsolutely\AIO\Traits\ModelTree;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;

final class CmsMenu extends NestedSetModel implements Sortable
{
    use ClearsResponseCache;
    use HasFactory;
    use ModelTree;
    use ScopePublished;
    use ScopeReference;
    use ScopeStatus;
    // use SortableTrait;

    protected $table = 'menus';

    protected $fillable = [
        'parent_id',
        'title',
        'reference',
        'remark',
        'url',
        'type',
        'icon',
        'thumbnail',
        'setting',
        'permission_key',
        'target',
        'is_external',
        'published_at',
        'expired_at',
        'status',
    ];

    protected $casts = [
        'parent_id'    => 'integer',
        'is_external'  => 'boolean',
        'type'         => MenuType::class,
        'target'       => MenuTarget::class,
        'setting'      => 'array',
        'published_at' => 'datetime',
        'expired_at'   => 'datetime',
        'status'       => Status::class,
    ];

    public function children(): HasMany
    {
        return $this->hasMany(CmsMenu::class, 'parent_id')->orderBy('left', 'asc')->with('children');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CmsMenu::class, 'parent_id');
    }
}
