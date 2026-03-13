<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Repositories\PageBlockGroupRepository;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Services\Contracts\BlockRendererServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageBlockSchemaServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageBlockServiceInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Coordinator service for page block operations
 *
 * This service coordinates block-related operations by composing:
 *
 * - PageBlockSchemaServiceInterface: Handles block schema validation and form configuration
 * - BlockRendererServiceInterface: Manages safe block rendering with error handling
 * - Repositories: Data access for blocks, groups, and settings
 *
 * Composition pattern:
 * 1. Retrieves block data from repositories
 * 2. Delegates schema operations to PageBlockSchemaService
 * 3. Delegates rendering to BlockRendererService
 * 4. Provides unified interface for block management
 *
 * This separation enables:
 * - Independent schema and rendering logic
 * - Easy testing of rendering without schema concerns
 * - Clear boundaries between data access, validation, and presentation
 */
final readonly class PageBlockService implements PageBlockServiceInterface
{
    public function __construct(
        protected PageBlockGroupRepository $groupRepository,
        protected PageBlockRepository $blockRepository,
        protected PageBlockSettingRepository $settingRepository,
        protected PageBlockSchemaServiceInterface $schemaService,
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
     * Get schema fields for a block
     */
    public function getSchemaFields(int $blockId): array
    {
        try {
            $block = $this->blockRepository->find($blockId);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return [];
        }

        if (! $block) {
            return [];
        }

        $schema     = $this->schemaService->getBlockSchema($block);
        $formConfig = $this->schemaService->generateFormConfig($schema);

        return $formConfig;
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
        throw new \Appsolutely\AIO\Exceptions\PageBlockRenderException($message);
    }
}
