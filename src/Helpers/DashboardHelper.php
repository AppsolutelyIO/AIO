<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Helpers;

use Illuminate\Support\Facades\File;

class DashboardHelper
{
    public static function imageThumbnail($filePath, $maxWidth = 100, $maxHeight = 100): string
    {
        $url = upload_url($filePath);

        $extension = strtolower(File::extension($filePath));

        $safeUrl = e($url);
        $html    = in_array($extension, FileHelper::IMAGE_EXTENSIONS) ?
            "<img src='{$safeUrl}' style='max-width:{$maxWidth}px;max-height:{$maxHeight}px;'>" :
            '<i class="fa fa-file-o"></i> ' . e($extension);

        return "<a href='{$safeUrl}' target='_blank'>{$html}</a>";
    }
}
