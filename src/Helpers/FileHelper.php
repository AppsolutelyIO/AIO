<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Helpers;

class FileHelper
{
    const DISPLAYABLE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/svg+xml',
        'image/webp',
        'text/plain',
        'text/html',
        'text/css',
        'text/javascript',
        'application/pdf',
    ];

    const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/svg+xml',
        'image/webp',
    ];

    const IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg',
        'webp',
    ];

    /**
     * Format file size to human-readable format
     *
     * @param  int  $bytes
     * @return string
     */
    public static function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
