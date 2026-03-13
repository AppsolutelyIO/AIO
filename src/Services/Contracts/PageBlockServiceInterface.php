<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services\Contracts;

use Appsolutely\AIO\Models\GeneralPage;
use Illuminate\Database\Eloquent\Collection;

interface PageBlockServiceInterface
{
    /**
     * Get categorised blocks
     */
    public function getCategorisedBlocks(): Collection;

    /**
     * Get published block settings for a page
     */
    public function getPublishedBlockSettings(int $pageId): Collection;

    /**
     * Update block setting publish status
     */
    public function updateBlockSettingPublishStatus(int $settingId, ?string $publishedAt = null, ?string $expiredAt = null): int;

    /**
     * Get schema fields for a block
     */
    public function getSchemaFields(int $blockId): array;

    /**
     * Render a block safely
     */
    public function renderBlockSafely($block, GeneralPage $page): string;
}
