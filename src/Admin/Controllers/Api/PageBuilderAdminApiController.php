<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Admin\Controllers\Api;

use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Services\BlockOptionFormRenderer;
use Appsolutely\AIO\Services\BlockOptionService;
use Appsolutely\AIO\Services\BlockRegistryService;
use Appsolutely\AIO\Services\Contracts\PageBlockSettingServiceInterface;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Appsolutely\AIO\Services\PageBlockService;
use Appsolutely\AIO\Services\PageBuilderDataEnricherService;
use Appsolutely\AIO\Services\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PageBuilderAdminApiController extends AdminBaseApiController
{
    public function __construct(
        protected PageService $pageService,
        protected PageBlockService $pageBlockService,
        protected BlockRegistryService $blockRegistryService,
        protected PageBuilderDataEnricherService $dataEnricherService,
        protected BlockOptionService $blockOptionService,
        protected BlockOptionFormRenderer $blockOptionFormRenderer,
        protected PageBlockSettingServiceInterface $blockSettingService,
        protected ThemeServiceInterface $themeService
    ) {}

    /**
     * Get page data for the builder.
     * Enriches project data with server-rendered HTML for each block
     * (reference -> block setting -> block value -> display_options/query_options -> HTML).
     */
    public function getPageData(Request $request, string $reference): JsonResponse
    {
        $page = $this->pageService->findByReference($reference);

        $setting  = $page->setting ?? [];
        $enriched = is_array($setting) ? $this->dataEnricherService->enrich($page, $setting) : $setting;

        $pageData = array_merge($page->toArray(), ['content' => $enriched]);

        return $this->success([
            'page' => $pageData,
        ]);
    }

    /**
     * Save page builder data
     */
    public function savePageData(Request $request, string $reference): JsonResponse
    {
        $data = $request->get('data');
        if (empty($data)) {
            return $this->error('Page data cannot be empty.');
        }

        $page = $this->pageService->saveSetting($reference, $data);

        return $this->success($data, 'Page saved successfully.');
    }

    /**
     * Reset page builder data
     */
    public function resetPageData(Request $request, string $reference): JsonResponse
    {
        $page = $this->pageService->resetSetting($reference);

        return $this->success(['page' => $page], __t('Page setting has been reset.'));
    }

    /**
     * Get available blocks registry from theme manifest.json.
     * Matches manifest component class to page_block to obtain block_id for correct saving.
     */
    public function getBlockRegistry(): JsonResponse
    {
        $data = $this->blockRegistryService->getRegistry();

        return $this->success($data);
    }

    /**
     * Get block options (display_options, query_options) for a single block.
     *
     * Saved block: ?block_setting_id=123 or ?reference=hero-abc123
     * Unsaved block: ?block_id=1&type=hero-banner
     *
     * Reference is unique in page_block_settings.
     */
    public function getBlockOption(Request $request): JsonResponse
    {
        $blockSettingId = $request->integer('block_setting_id', 0);
        $reference      = (string) $request->string('reference', '')->trim();
        $blockId        = $request->integer('block_id', 0);
        $type           = (string) $request->string('type', '')->trim();

        if ($blockSettingId > 0) {
            $config = $this->blockOptionService->getOptionsBySettingId($blockSettingId);
        } elseif ($reference !== '') {
            $config = $this->blockOptionService->getOptionsByReference($reference);
        } elseif ($blockId > 0 && $type !== '') {
            $config = $this->blockOptionService->getOptionsByBlockIdAndType($blockId, $type);
        } else {
            return $this->failValidation(['param' => [__t('Provide block_setting_id or reference (saved), or block_id + type (unsaved).')]]);
        }

        if ($config === null) {
            return $this->error('Block not found.');
        }

        $config['display_options_html'] = $this->blockOptionFormRenderer->render(
            $config['display_options_definition'] ?? [],
            $config['display_options'] ?? []
        );

        $config['query_options_html'] = $this->blockOptionFormRenderer->render(
            $config['query_options_definition'] ?? [],
            $config['query_options'] ?? []
        );

        return $this->success($config);
    }

    /**
     * Save display_options and query_options for a block (identified by reference).
     *
     * PATCH body: { reference, display_options?, query_options? }
     */
    public function updateBlockOption(Request $request): JsonResponse
    {
        $reference      = (string) $request->string('reference', '')->trim();
        $displayOptions = $request->input('display_options', []);
        $queryOptions   = $request->input('query_options', []);

        if ($reference === '') {
            return $this->failValidation(['reference' => [__t('reference is required.')]]);
        }

        if (! is_array($displayOptions)) {
            $displayOptions = [];
        }

        if (! is_array($queryOptions)) {
            $queryOptions = [];
        }

        $saved = $this->blockOptionService->saveOptions($reference, $displayOptions, $queryOptions);

        if (! $saved) {
            return $this->error(__t('Block not found.'));
        }

        return $this->success([], __t('Block options saved successfully.'));
    }

    /**
     * Get block HTML by block identifier (uses stored config).
     *
     * Params: block_setting_id | reference | (block_id + type)
     * Optional: reference (for unsaved block's Livewire mount)
     */
    public function getBlockHtml(Request $request): JsonResponse
    {
        $blockSettingId = $request->integer('block_setting_id', 0);
        $reference      = (string) $request->string('reference', '')->trim();
        $blockId        = $request->integer('block_id', 0);
        $type           = (string) $request->string('type', '')->trim();

        if ($blockSettingId > 0) {
            $config = $this->blockOptionService->getOptionsBySettingId($blockSettingId);
        } elseif ($reference !== '') {
            $config = $this->blockOptionService->getOptionsByReference($reference);
        } elseif ($blockId > 0 && $type !== '') {
            $config = $this->blockOptionService->getOptionsByBlockIdAndType($blockId, $type);
        } else {
            return $this->failValidation(['param' => [__t('Provide block_setting_id or reference (saved), or block_id + type (unsaved).')]]);
        }

        if ($config === null) {
            return $this->error('Block not found.');
        }

        $ref  = $config['reference'] !== '' ? $config['reference'] : $reference;
        $html = $this->blockRegistryService->renderBlockPreview(
            $config['type'],
            $config['display_options'],
            $config['query_options'],
            $ref,
            []
        );

        return $this->success(['html' => $html]);
    }

    /**
     * Render a single block with custom options (for real-time preview when form changes).
     *
     * POST body: { type, display_options?, query_options?, reference?, page_data? }
     */
    public function renderBlockWithOptions(Request $request): JsonResponse
    {
        $type = (string) $request->string('type', '')->trim();

        if ($type === '') {
            return $this->failValidation(['type' => [__t('type is required.')]]);
        }

        $displayOptions = $request->input('display_options', []);
        $queryOptions   = $request->input('query_options', []);
        $reference      = (string) $request->string('reference', '')->trim();

        if (! is_array($displayOptions)) {
            $displayOptions = [];
        }

        if (! is_array($queryOptions)) {
            $queryOptions = [];
        }

        $pageData = $request->input('page_data', []);
        if (! is_array($pageData)) {
            $pageData = [];
        }

        $html = $this->blockRegistryService->renderBlockPreview(
            $type,
            $displayOptions,
            $queryOptions,
            $reference,
            $pageData
        );

        return $this->success(['html' => $html]);
    }

    /**
     * Get available themes with block counts for syncing to current theme.
     *
     * GET /api/pages/{reference}/theme-sync
     */
    public function getThemeSyncOptions(Request $request, string $reference): JsonResponse
    {
        $page         = $this->pageService->findByReference($reference);
        $currentTheme = $this->themeService->resolveThemeName();

        if ($currentTheme === null) {
            return $this->error('No active theme found.');
        }

        $options = $this->blockSettingService->getAvailableThemesForSync($page->id, $currentTheme);

        return $this->success([
            'current_theme' => $currentTheme,
            'options'       => $options,
        ]);
    }

    /**
     * Sync block settings from a source theme to the current theme.
     *
     * POST /api/pages/{reference}/theme-sync
     * Body: { source_theme: "june" }
     */
    public function syncThemeBlocks(Request $request, string $reference): JsonResponse
    {
        $sourceTheme = (string) $request->string('source_theme', '')->trim();

        if ($sourceTheme === '') {
            return $this->failValidation(['source_theme' => ['source_theme is required.']]);
        }

        $page         = $this->pageService->findByReference($reference);
        $currentTheme = $this->themeService->resolveThemeName();

        if ($currentTheme === null) {
            return $this->error('No active theme found.');
        }

        if ($sourceTheme === $currentTheme) {
            return $this->failValidation(['source_theme' => ['Cannot sync from the same theme.']]);
        }

        $result = $this->blockSettingService->syncFromTheme($page->id, $sourceTheme, $currentTheme);

        return $this->success($result, "Synced {$result['synced']} blocks, skipped {$result['skipped']}.");
    }
}
