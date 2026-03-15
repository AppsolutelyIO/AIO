<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Constants\BasicConstant;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Repositories\PageRepository;
use Appsolutely\AIO\Services\Contracts\PageBlockSettingServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageStructureServiceInterface;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * Service for core page operations
 *
 * This service handles basic page operations:
 * - Finding pages by slug, ID, or reference
 * - Managing page settings (save/reset)
 *
 * Block settings sync is delegated to PageBlockSettingService.
 * Page structure generation is delegated to PageStructureService.
 */
final readonly class PageService implements PageServiceInterface
{
    public function __construct(
        protected PageRepository $pageRepository,
        protected PageBlockSettingRepository $pageBlockSettingRepository,
        protected PageBlockSettingServiceInterface $blockSettingService,
        protected PageStructureServiceInterface $structureService,
        protected ThemeServiceInterface $themeService
    ) {}

    public function findPublishedPage(string $slug): ?Page
    {
        $page = $this->pageRepository->findPageBySlug($slug, now());
        if ($page !== null) {
            $this->filterPageBlocksByTheme($page);
        }

        return $page;
    }

    public function findPublishedPageById(int $id): ?Page
    {
        $page = $this->pageRepository->findPageById($id, now());
        if ($page !== null) {
            $this->filterPageBlocksByTheme($page);
        }

        return $page;
    }

    public function findByReference(string $reference): Model
    {
        $page = $this->pageRepository->with(['blocks.block', 'blocks.blockValue'])->reference($reference)->firstOrFail();
        $this->filterPageBlocksByTheme($page);

        return $page;
    }

    public function resetSetting(string $reference): Model
    {
        $page  = $this->findByReference($reference);
        $theme = $this->themeService->resolveThemeName();
        $this->pageRepository->updateSetting($page->id, []);
        $this->pageBlockSettingRepository->resetSetting($page->id, $theme);

        return $this->pageRepository->find($page->id);
    }

    public function saveSetting(string $reference, array $data): Model
    {
        $page  = $this->findByReference($reference);
        $theme = $this->themeService->resolveThemeName();

        // Extract block data from GrapesJS structure
        $blockData = Arr::get($data, BasicConstant::PAGE_GRAPESJS_KEY);
        if (empty($blockData) || ! is_array($blockData)) {
            $blockData = [];
        }

        // Reset existing settings for current theme and sync new ones
        $this->pageBlockSettingRepository->resetSetting($page->id, $theme);
        $this->blockSettingService->syncSettings($blockData, $page->id);

        // Update page settings
        $this->pageRepository->updateSetting($page->id, $data);

        return $this->pageRepository->find($page->id);
    }

    /**
     * Sync page block settings (delegates to PageBlockSettingService)
     */
    public function syncSettings(array $data, int $pageId): array
    {
        return $this->blockSettingService->syncSettings($data, $pageId);
    }

    /**
     * Get block value ID (delegates to PageBlockSettingService)
     */
    public function getBlockValueId(int $blockId): int
    {
        return $this->blockSettingService->getBlockValueId($blockId);
    }

    /**
     * Generate default page setting structure (delegates to PageStructureService)
     */
    public function generateDefaultPageSetting(): array
    {
        return $this->structureService->generateDefaultPageSetting();
    }

    /**
     * Filter page blocks to only those matching the current theme.
     * Keeps settings where theme is null (theme-agnostic) or equals current theme.
     */
    protected function filterPageBlocksByTheme(Page $page): void
    {
        $theme  = $this->themeService->resolveThemeName();
        $blocks = $page->getRelation('blocks');

        if ($blocks === null || $blocks->isEmpty()) {
            return;
        }

        $filtered = $blocks->filter(function ($setting) use ($theme) {
            $settingTheme = $setting->theme;

            if ($settingTheme === null || $settingTheme === '') {
                return true;
            }

            return $theme !== null && $settingTheme === $theme;
        });

        $page->setRelation('blocks', $filtered->sortBy('sort')->values());
    }
}
