<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Livewire\Anchor;
use Appsolutely\AIO\Models\PageBlock;
use Appsolutely\AIO\Models\PageBlockGroup;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Services\Concerns\ResolvesLivewireClassName;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Livewire;
use Qirolab\Theme\Theme;

/**
 * Builds block registry from theme manifest.json, matching blocks to page_block by class
 * to obtain block_id for correct saving of page design data.
 *
 * The manifest is the source of truth for available blocks per theme.
 * page_block lookup by class provides block_id and grouping.
 *
 * Block content (HTML) is rendered from local theme templates, not from page_block.template.
 * displayOptions from manifest; queryOptions from page_blocks (cached).
 */
final readonly class BlockRegistryService
{
    use ResolvesLivewireClassName;
    private const CACHE_KEY_PREFIX = 'block_registry:';

    private const CACHE_TTL = 3600;

    private const DEFAULT_TAG_NAME = 'section';

    public function __construct(
        protected ManifestServiceInterface $manifestService,
        protected PageBlockRepository $blockRepository
    ) {}

    /**
     * Get block registry from active theme manifest, with block_id from page_block lookup.
     *
     * @return array<int, array<string, mixed>> Groups with blocks, same schema as getCategorisedBlocks
     */
    public function getRegistry(?string $themeName = null): array
    {
        $themeName = $themeName ?? Theme::active();
        $cacheKey  = self::CACHE_KEY_PREFIX . $themeName;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($themeName) {
            return $this->buildRegistry($themeName);
        });
    }

    private function buildRegistry(string $themeName): array
    {
        $manifest  = $this->manifestService->loadManifest($themeName);
        $templates = $manifest['templates'] ?? [];

        if (empty($templates)) {
            return [];
        }

        $blocksByClass = $this->blockRepository->getBlocksByClass();

        $registryBlocks = [];
        foreach ($templates as $manifestRef => $config) {
            $component = $config['component'] ?? null;
            if (empty($component) || ! is_string($component)) {
                continue;
            }

            $resolvedComponent = $this->resolveClassName($component);

            $pageBlock = $blocksByClass[$component] ?? null;
            if ($pageBlock === null) {
                continue;
            }

            if (! class_exists($resolvedComponent)) {
                continue;
            }

            $component = $resolvedComponent;

            $registryBlocks[] = [
                'page_block'     => $pageBlock,
                'manifest_ref'   => $manifestRef,
                'label'          => $config['label'] ?? $pageBlock->title,
                'description'    => $config['description'] ?? $pageBlock->description ?? '',
                'view'           => $config['view'] ?? $manifestRef,
                'displayOptions' => $config['displayOptions'] ?? [],
                'component'      => $component,
            ];
        }

        return $this->groupAndFormat($registryBlocks);
    }

    /**
     * Clear block registry cache (e.g. when page_blocks are updated).
     */
    public function clearCache(?string $themeName = null): void
    {
        if ($themeName !== null) {
            Cache::forget(self::CACHE_KEY_PREFIX . $themeName);

            return;
        }

        $themeName = Theme::active();
        Cache::forget(self::CACHE_KEY_PREFIX . $themeName);
    }

    /**
     * @param  array<int, array{page_block: PageBlock, manifest_ref: string, label: string, description: string, view: string, displayOptions: array, component: string}>  $registryBlocks
     * @return array<int, array<string, mixed>>
     */
    private function groupAndFormat(array $registryBlocks): array
    {
        $grouped = collect($registryBlocks)->groupBy(fn (array $item) => $item['page_block']->block_group_id);

        $groupIds = $grouped->keys()->filter()->values()->toArray();
        if (empty($groupIds)) {
            return [];
        }

        $groups = PageBlockGroup::query()
            ->whereIn('id', $groupIds)
            ->status()
            ->orderBy('sort')
            ->get();

        $result = [];
        foreach ($groups as $group) {
            $items  = $grouped->get($group->id, collect());
            $blocks = $items->map(function (array $item) {
                $block = $item['page_block'];
                $arr   = $block->toArray();

                return array_merge($arr, [
                    'label'   => $item['label'],
                    'type'    => $item['manifest_ref'],
                    'content' => $this->renderBlockContent($item),
                    'tagName' => self::DEFAULT_TAG_NAME,
                ]);
            })->sortBy(fn (array $b) => $b['sort'] ?? 0)->values()->all();

            if (empty($blocks)) {
                continue;
            }

            $result[] = [
                'id'         => $group->id,
                'title'      => $group->title,
                'remark'     => $group->remark,
                'sort'       => $group->sort,
                'status'     => $group->status,
                'created_at' => $group->created_at?->toIso8601String(),
                'updated_at' => $group->updated_at?->toIso8601String(),
                'blocks'     => $blocks,
            ];
        }

        return $result;
    }

    /**
     * Render block HTML from local theme template (not from database).
     *
     * @param  array{page_block: PageBlock, manifest_ref: string, view: string, displayOptions: array, component: string}  $item
     */
    private function renderBlockContent(array $item): string
    {
        $component      = $item['component'];
        $viewName       = $item['view'];
        $displayOptions = $item['displayOptions'] ?? [];
        $manifestRef    = $item['manifest_ref'];
        $pageBlock      = $item['page_block'];
        $queryOptions   = $pageBlock->query_options ?? [];

        if (! is_array($queryOptions)) {
            $queryOptions = [];
        }

        if (! class_exists($component) || ! is_subclass_of($component, Component::class)) {
            return $this->buildPlaceholder($manifestRef);
        }

        try {
            $data = [
                'page'           => [],
                'viewName'       => $viewName,
                'viewStyle'      => 'default',
                'queryOptions'   => $queryOptions,
                'displayOptions' => $displayOptions,
                'blockSort'      => 0,
            ];

            if ($component === Anchor::class) {
                $data['blocksForAnchor'] = [];
            }

            $reference = 'registry-preview-' . $manifestRef;

            return Livewire::mount($component, $data, $reference);
        } catch (\Throwable) {
            return $this->buildPlaceholder($manifestRef);
        }
    }

    /**
     * Render a single block with custom options (for real-time preview in Page Builder).
     *
     * @param  array<string, mixed>  $pageData  Optional page data for blocks that need it (e.g. Anchor)
     */
    public function renderBlockPreview(
        string $type,
        array $displayOptions = [],
        array $queryOptions = [],
        string $reference = '',
        array $pageData = []
    ): string {
        $themeName = Theme::active();
        $config    = $this->manifestService->getTemplateConfig($type, $themeName);

        if ($config === null) {
            return $this->buildPlaceholder($type);
        }

        $component = $config['component'] ?? null;
        if (empty($component) || ! is_string($component) || ! class_exists($component)) {
            return $this->buildPlaceholder($type);
        }

        if (! is_subclass_of($component, Component::class)) {
            return $this->buildPlaceholder($type);
        }

        $viewName = $config['view'] ?? $type;
        $ref      = $reference !== '' ? $reference : 'preview-' . $type;

        $data = [
            'page'           => $pageData,
            'viewName'       => $viewName,
            'viewStyle'      => 'default',
            'queryOptions'   => $queryOptions,
            'displayOptions' => $displayOptions,
            'blockSort'      => 0,
        ];

        if ($component === Anchor::class) {
            $data['blocksForAnchor'] = [];
        }

        try {
            return Livewire::mount($component, $data, $ref);
        } catch (\Throwable) {
            return $this->buildPlaceholder($type);
        }
    }

    /**
     * Build a simple placeholder when template cannot be found or rendering fails.
     */
    private function buildPlaceholder(string $blockName): string
    {
        $safe = htmlspecialchars($blockName, ENT_QUOTES, 'UTF-8');

        return '<section class="block-' . $safe . '"><div class="container"><p>' . $safe . '</p></div></section>';
    }
}
