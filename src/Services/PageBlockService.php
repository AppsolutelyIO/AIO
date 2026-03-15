<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Exceptions\PageBlockRenderException;
use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Repositories\PageBlockGroupRepository;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Services\Contracts\BlockRendererServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageBlockServiceInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Coordinator service for page block operations
 *
 * Composes BlockRendererService for rendering and repositories for data access.
 */
final readonly class PageBlockService implements PageBlockServiceInterface
{
    public function __construct(
        protected PageBlockGroupRepository $groupRepository,
        protected PageBlockRepository $blockRepository,
        protected PageBlockSettingRepository $settingRepository,
        protected BlockRendererServiceInterface $blockRendererService
    ) {}

    public function getCategorisedBlocks(): Collection
    {
        return $this->groupRepository->getCategorisedBlocks();
    }

    public function getPublishedBlockSettings(int $pageId): Collection
    {
        return $this->settingRepository->getActivePublishedSettings($pageId);
    }

    public function updateBlockSettingPublishStatus(int $settingId, ?string $publishedAt = null, ?string $expiredAt = null): int
    {
        return $this->settingRepository->updatePublishStatus($settingId, $publishedAt, $expiredAt);
    }

    /**
     * Validate and render a block safely
     * Returns the rendered HTML or error message
     */
    public function renderBlockSafely($block, GeneralPage $page): string
    {
        return $this->blockRendererService->renderBlockSafely($block, $page);
    }

    /**
     * Get HTML for block errors (only in debug mode)
     */
    private function getBlockErrorHtml(string $message): string
    {
        if (! config('app.debug')) {
            return ''; // Return empty string in production
        }

        // In debug mode, throw exception to show error details
        throw new PageBlockRenderException($message);
    }
}
