<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\ReleaseChannel;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\ScopePublished;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ReleaseVersion extends Model
{
    use ScopePublished;
    use ScopeStatus;
    use SoftDeletes;

    protected $fillable = [
        'version',
        'remark',
        'release_channel',
        'status',
        'published_at',
    ];

    protected $casts = [
        'release_channel' => ReleaseChannel::class,
        'status'          => Status::class,
        'published_at'    => 'datetime',
    ];

    public function builds(): HasMany
    {
        return $this->hasMany(ReleaseBuild::class, 'version_id');
    }
}
