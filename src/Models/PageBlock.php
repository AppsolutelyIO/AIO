<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\ScopeReference;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class PageBlock extends Model
{
    use ScopeReference;
    use ScopeStatus;

    protected $fillable = [
        'block_group_id',
        'title',
        'reference',
        'class',
        'remark',
        'description',
        'template',
        'instruction',
        'scope',
        'schema',
        'query_options',
        'query_options_definition',
        'droppable',
        'setting',
        'sort',
        'status',
    ];

    protected $casts = [
        'schema'                   => 'array',
        'query_options'            => 'array',
        'query_options_definition' => 'array',
        'setting'                  => 'array',
        'droppable'                => 'integer',
        'sort'                     => 'integer',
        'status'                   => Status::class,
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(PageBlockGroup::class, 'block_group_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(PageBlockSetting::class, 'block_id');
    }

    public function blockValue(): HasOne
    {
        return $this->hasOne(PageBlockValue::class, 'block_id');
    }
}
