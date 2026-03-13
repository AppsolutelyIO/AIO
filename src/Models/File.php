<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

use Appsolutely\AIO\Helpers\FileHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'original_filename',
        'filename',
        'extension',
        'mime_type',
        'path',
        'size',
        'hash',
        'width',
        'height',
        'disk',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size'       => 'integer',
            'width'      => 'integer',
            'height'     => 'integer',
            'metadata'   => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    public function getFullPathAttribute(): string
    {
        return $this->path . '/' . $this->filename;
    }

    /**
     * Check if this file is an image.
     */
    public function isImage(): bool
    {
        return in_array(
            strtolower($this->extension),
            FileHelper::IMAGE_EXTENSIONS,
            true
        );
    }

    /**
     * Get all file attachment records for this file.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(FileAttachment::class);
    }
}
