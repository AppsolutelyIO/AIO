<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Constants\BasicConstant;
use Appsolutely\AIO\Models\GeneralPage;
use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Illuminate\Support\Arr;
use Qirolab\Theme\Theme;

/**
 * Enriches Page Builder project data by injecting server-rendered HTML for each block.
 *
 * Flow:
 * 1. Read components from JSON (pages.0.frames.0.component.components)
 * 2. For each component: get reference → find block setting by page_id + reference
 * 3. Get block value (display_options, query_options) from block setting
 * 4. Render HTML via BlockRendererService
 * 5. Inject content into component
 */
final readonly class PageBuilderDataEnricherService
{
    public function __construct(
        protected BlockRendererService $blockRendererService,
        protected PageBlockSettingRepository $blockSettingRepository,
        protected ThemeServiceInterface $themeService
    ) {}

    /**
     * Enrich project data with rendered block HTML.
     *
     * @param  array<string, mixed>  $setting  GrapesJS project data (from page.setting)
     */
    public function enrich(Page $page, array $setting): array
    {
        $components = Arr::get($setting, BasicConstant::PAGE_GRAPESJS_KEY);

        if (! is_array($components)) {
            return $setting;
        }

        $this->ensureThemeSetup();

        $generalPage = new GeneralPage($page);

        $enriched = [];
        foreach ($components as $component) {
            $result = $this->enrichComponent($component, $page->id, $generalPage);
            if ($result !== null) {
                $enriched[] = $result;
            }
        }

        Arr::set($setting, BasicConstant::PAGE_GRAPESJS_KEY, $enriched);

        return $setting;
    }

    /**
     * Enrich a single component with rendered HTML.
     *
     * @param  array<string, mixed>  $component  GrapesJS component data
     * @return array<string, mixed>|null Component with content (HTML) injected, or null if no matching setting
     */
    /**
     * Ensure theme view paths are registered (needed in admin context where ApplyThemeMiddleware skips).
     */
    private function ensureThemeSetup(): void
    {
        if (Theme::active() !== null) {
            return;
        }

        $themeName = $this->themeService->resolveThemeName();
        if ($themeName === null) {
            return;
        }

        $parentTheme = $this->themeService->getParentTheme();
        $this->themeService->setupTheme($themeName, $parentTheme);
    }

    private function enrichComponent(array $component, int $pageId, GeneralPage $generalPage): ?array
    {
        $reference = $component['reference'] ?? $component['attributes']['reference'] ?? null;
        $blockId   = $component['block_id'] ?? $component['attributes']['block_id'] ?? null;

        if (empty($reference)) {
            return null;
        }

        $theme   = $this->themeService->resolveThemeName();
        $setting = $this->blockSettingRepository->findBy($pageId, $blockId, $reference, $theme);

        if ($setting === null) {
            return null;
        }

        $setting->load(['block', 'blockValue']);

        $html = $this->blockRendererService->renderBlockSafely($setting, $generalPage);

        $component['content'] = $html;

        return $component;
    }
}
