<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\BuildStatus;
use Appsolutely\AIO\Enums\Platform;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\HasFilesOfType;
use Appsolutely\AIO\Models\Concerns\ScopePublished;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ReleaseBuild extends Model
{
    use HasFilesOfType;
    use ScopePublished;
    use ScopeStatus;
    use SoftDeletes;

    protected $fillable = [
        'version_id',
        'platform',
        'arch',
        'path',
        'force_update',
        'gray_strategy',
        'release_notes',
        'build_status',
        'build_log',
        'file_attachment_id',
        'signature',
        'status',
        'published_at',
    ];

    protected $casts = [
        'platform'      => Platform::class,
        'build_status'  => BuildStatus::class,
        'force_update'  => 'integer',
        'gray_strategy' => 'array',
        'status'        => Status::class,
        'published_at'  => 'datetime',
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(ReleaseVersion::class, 'version_id');
    }

    public function fileAttachment(): BelongsTo
    {
        return $this->belongsTo(FileAttachment::class);
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if (! empty($this->path)) {
            $path = $this->path;
        } elseif ($this->fileAttachment && $this->fileAttachment->file) {
            $path = $this->fileAttachment->file->full_path;
        }
        if (empty($path)) {
            return null;
        }

        return public_url($path);
    }
}
