<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models\Concerns;

use Appsolutely\AIO\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasFilesOfType
{
    /**
     * Get files of a specific type for this model.
     *
     * @return MorphToMany
     */
    public function filesOfType(string $type)
    {
        return $this->morphToMany(File::class, 'attachable', 'file_attachments')
            ->wherePivot('type', $type)
            ->withTimestamps()
            ->withPivot([
                'type',
                'file_path',
                'optimized_path',
                'optimized_format',
                'optimized_size',
                'optimized_width',
                'optimized_height',
                'sort_order',
                'title',
                'description',
                'config',
                'status',
            ]);
    }

    /**
     * Get files of a specific type ordered by sort_order.
     *
     * @return MorphToMany
     */
    public function orderedFilesOfType(string $type)
    {
        return $this->filesOfType($type)
            ->orderByPivot('sort_order');
    }
}
