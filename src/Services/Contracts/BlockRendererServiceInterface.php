<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\PageBlockSetting;

interface BlockRendererServiceInterface
{
    /**
     * Validate and render a block safely
     * Returns the rendered HTML or error message
     */
    public function renderBlockSafely(PageBlockSetting $block, GeneralPage $page): string;
}
