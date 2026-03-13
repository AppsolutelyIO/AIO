<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models;

class AdminSetting extends Setting
{
    const PATH_PATTERNS = [
        'basic-logo'    => 'appearance.logo_pattern',
        'basic-favicon' => 'appearance.favicon_pattern',
    ];

    public function filesOfType(string $type)
    {
        return $this->morphToMany(File::class, 'attachable', 'file_attachments')
            ->wherePivot('type', $type)
            ->withTimestamps()
            ->withPivot('type');
    }
}
