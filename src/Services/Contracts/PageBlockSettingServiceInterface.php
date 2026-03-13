<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

interface PageBlockSettingServiceInterface
{
    /**
     * Sync page block settings from GrapesJS data
     *
     * @param  array  $data  Array of block setting data from page builder
     * @param  int  $pageId  The page ID to sync settings for
     * @return array Array of created/updated block settings
     */
    public function syncSettings(array $data, int $pageId): array;

    /**
     * Get or create block value ID for a block and optional theme.
     * Reuses existing block value for the same block and theme when possible.
     * Exception: GeneralBlock always creates a new value (used by many templates).
     *
     * @param  int  $blockId  The block ID
     * @param  string|null  $theme  The theme name (null = theme-agnostic)
     * @param  string  $view  Template name from manifest (for new block values)
     * @return int The block value ID
     */
    public function getBlockValueId(int $blockId, ?string $theme = null, string $view = ''): int;
}
