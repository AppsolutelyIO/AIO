<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;

/**
 * Fetches and saves block options (display_options, query_options) for a single block.
 *
 * Handles three scenarios:
 * 1. Saved by ID: block_setting_id → page_block_settings → block_value
 * 2. Saved by reference: reference → page_block_settings (reference is unique in the table)
 * 3. Unsaved: block_id + type → manifest.json (displayOptions) + page_blocks (query_options)
 */
final readonly class BlockOptionService
{
    public function __construct(
        protected PageBlockSettingRepository $settingRepository,
        protected PageBlockRepository $blockRepository,
        protected ManifestServiceInterface $manifestService
    ) {}

    /**
     * Get block options by block setting ID (saved block).
     *
     * @return array{
     *     block_id: int,
     *     type: string,
     *     reference: string,
     *     display_options: array,
     *     query_options: array,
     *     block_value_id: int|null,
     *     view: string,
     *     view_style: string,
     *     display_options_definition: array,
     *     query_options_definition: array
     * }|null
     */
    public function getOptionsBySettingId(int $blockSettingId): ?array
    {
        $setting = $this->settingRepository->find($blockSettingId);

        if ($setting === null || ! $setting instanceof PageBlockSetting) {
            return null;
        }

        $setting->load(['block', 'blockValue']);

        $block      = $setting->block;
        $blockValue = $setting->blockValue;

        if ($block === null) {
            return null;
        }

        $type = $setting->type ?? $block->reference ?? '';

        // Resolve template options from manifest (for display options definition)
        $viewKey = $blockValue?->view ?? $block->reference ?? $type;
        $theme   = $blockValue?->theme;

        $templateConfig = $viewKey !== ''
            ? $this->manifestService->getTemplateConfig($viewKey, $theme)
            : null;

        $displayOptionsDefinition = [];
        if (is_array($templateConfig)) {
            $displayOptionsDefinition = $templateConfig['displayOptionsDefinition'] ?? [];
        }

        // Query options definition comes from page_blocks.query_options_definition
        $queryOptionsDefinition = [];
        $blockQueryDefinition   = $block->query_options_definition ?? [];
        if (is_string($blockQueryDefinition)) {
            $decoded                = json_decode($blockQueryDefinition, true);
            $queryOptionsDefinition = is_array($decoded) ? $decoded : [];
        } elseif (is_array($blockQueryDefinition)) {
            $queryOptionsDefinition = $blockQueryDefinition;
        }

        return [
            'block_id'                   => $block->id,
            'type'                       => $type,
            'reference'                  => (string) ($setting->reference ?? ''),
            'display_options'            => $setting->displayOptionsValue ?? [],
            'query_options'              => $setting->queryOptionsValue ?? [],
            'block_value_id'             => $blockValue?->id,
            'view'                       => (string) ($blockValue?->view ?? $this->getViewFromManifest($type)),
            'view_style'                 => (string) ($blockValue?->view_style ?? 'default'),
            'display_options_definition' => $displayOptionsDefinition,
            'query_options_definition'   => $queryOptionsDefinition,
        ];
    }

    /**
     * Get block options by page_block_settings.reference (saved block).
     * Reference is unique in page_block_settings.
     *
     * @return array{
     *     block_id: int,
     *     type: string,
     *     reference: string,
     *     display_options: array,
     *     query_options: array,
     *     block_value_id: int|null,
     *     view: string,
     *     view_style: string,
     *     display_options_definition: array,
     *     query_options_definition: array
     * }|null
     */
    public function getOptionsByReference(string $reference): ?array
    {
        $setting = $this->settingRepository->findBy(null, null, $reference);

        if ($setting === null || ! $setting instanceof PageBlockSetting) {
            return null;
        }

        return $this->getOptionsBySettingId($setting->id);
    }

    /**
     * Get block options by block_id + type (unsaved block).
     *
     * @return array{
     *     block_id: int,
     *     type: string,
     *     reference: string,
     *     display_options: array,
     *     query_options: array,
     *     block_value_id: null,
     *     view: string,
     *     view_style: string,
     *     display_options_definition: array,
     *     query_options_definition: array
     * }|null
     */
    public function getOptionsByBlockIdAndType(int $blockId, string $type): ?array
    {
        $block = $this->blockRepository->find($blockId);

        if ($block === null) {
            return null;
        }

        $config = $this->manifestService->getTemplateConfig($type);

        if ($config === null) {
            return null;
        }

        $component = $config['component'] ?? null;
        if ($component !== null && $block->class !== $component) {
            return null;
        }

        $displayOptions = $config['displayOptions'] ?? [];
        $view           = $config['view'] ?? $type;

        $displayOptionsDefinition = $config['displayOptionsDefinition'] ?? [];

        $queryOptions = $block->query_options ?? [];
        if (! is_array($queryOptions)) {
            $queryOptions = is_string($queryOptions) ? (json_decode($queryOptions, true) ?? []) : [];
        }

        $queryOptionsDefinition = [];
        $blockQueryDefinition   = $block->query_options_definition ?? [];
        if (is_string($blockQueryDefinition)) {
            $decoded                = json_decode($blockQueryDefinition, true);
            $queryOptionsDefinition = is_array($decoded) ? $decoded : [];
        } elseif (is_array($blockQueryDefinition)) {
            $queryOptionsDefinition = $blockQueryDefinition;
        }

        return [
            'block_id'                   => $block->id,
            'type'                       => $type,
            'reference'                  => '',
            'display_options'            => $displayOptions,
            'query_options'              => $queryOptions,
            'block_value_id'             => null,
            'view'                       => (string) $view,
            'view_style'                 => 'default',
            'display_options_definition' => $displayOptionsDefinition,
            'query_options_definition'   => $queryOptionsDefinition,
        ];
    }

    /**
     * Save display_options and query_options for a block setting identified by reference.
     *
     * Uses PageBlockSetting::checkAndCreateNewBlockValue() to handle shared block values —
     * if the block value is shared with other settings, a new copy is created automatically.
     */
    public function saveOptions(string $reference, array $displayOptions, array $queryOptions): bool
    {
        $setting = $this->settingRepository->findBy(null, null, $reference);

        if ($setting === null) {
            return false;
        }

        $setting->load(['block', 'blockValue']);

        if ($setting->blockValue === null) {
            return false;
        }

        $setting->blockValue->display_options = $displayOptions;
        $setting->blockValue->query_options   = $queryOptions;

        $setting->checkAndCreateNewBlockValue();
        $setting->save();

        return true;
    }

    private function getViewFromManifest(string $type): string
    {
        $config = $this->manifestService->getTemplateConfig($type);

        return (string) ($config['view'] ?? $type);
    }
}
