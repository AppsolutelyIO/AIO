<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Concerns\ScopePublished;
use Appsolutely\AIO\Models\Concerns\ScopeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileAttachment extends Model
{
    use HasFactory;
    use ScopePublished;
    use ScopeStatus;

    /**
     * The table associated with the model.
     */
    protected $table = 'file_attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'file_id',
        'attachable_type',
        'attachable_id',
        'type',
        'file_path',
        'optimized_path',
        'optimized_format',
        'optimized_size',
        'optimized_width',
        'optimized_height',
        'title',
        'keyword',
        'description',
        'content',
        'config',
        'status',
        'sort_order',
        'published_at',
        'expired_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config'           => 'array',
            'status'           => Status::class,
            'optimized_size'   => 'integer',
            'optimized_width'  => 'integer',
            'optimized_height' => 'integer',
            'sort_order'       => 'integer',
            'published_at'     => 'datetime',
            'expired_at'       => 'datetime',
        ];
    }

    /**
     * Get the file that owns the attachment.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Check if an optimized version exists.
     */
    public function hasOptimizedVersion(): bool
    {
        return ! empty($this->optimized_path);
    }

    /**
     * Get the best available file path (optimized or original).
     */
    public function getServingPathAttribute(): string
    {
        return $this->optimized_path ?? $this->file->full_path;
    }
}
