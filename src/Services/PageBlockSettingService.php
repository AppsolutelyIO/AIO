<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Services;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Livewire\GeneralBlock;
use Appsolutely\AIO\Models\PageBlockSetting;
use Appsolutely\AIO\Repositories\PageBlockRepository;
use Appsolutely\AIO\Repositories\PageBlockSettingRepository;
use Appsolutely\AIO\Repositories\PageBlockValueRepository;
use Appsolutely\AIO\Services\Contracts\ManifestServiceInterface;
use Appsolutely\AIO\Services\Contracts\PageBlockSettingServiceInterface;
use Appsolutely\AIO\Services\Contracts\ThemeServiceInterface;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use PDOException;

/**
 * Service for managing page block settings synchronization
 *
 * This service handles the synchronization of block settings from the page builder
 * to the database. It manages:
 *
 * - Syncing block settings from GrapesJS data structure
 * - Creating/updating block settings with proper sort order
 * - Managing block values (reusing existing or creating new)
 *
 * Separated from PageService to follow Single Responsibility Principle.
 */
final readonly class PageBlockSettingService implements PageBlockSettingServiceInterface
{
    public function __construct(
        protected PageBlockRepository $pageBlockRepository,
        protected PageBlockValueRepository $pageBlockValueRepository,
        protected PageBlockSettingRepository $pageBlockSettingRepository,
        protected ManifestServiceInterface $manifestService,
        protected ThemeServiceInterface $themeService,
        protected ConnectionInterface $db
    ) {}

    public function syncSettings(array $data, int $pageId): array
    {
        try {
            $result = [];
            // Wrap all operations in a transaction to ensure data consistency
            // If any block setting fails, the entire operation is rolled back
            $this->db->transaction(function () use ($data, &$result, $pageId) {
                // Process each block setting in order (sort order is based on array index)
                foreach ($data as $index => $setting) {
                    $sort = $index + 1; // Sort order starts at 1, not 0
                    $item = $this->syncBlockSettingItem($setting, $sort, $pageId);
                    // Skip invalid or duplicate items (empty array returned)
                    if (empty($item)) {
                        continue;
                    }
                    $result[] = $item;
                }
            });

            return $result;
        } catch (QueryException|PDOException $exception) {
            log_error(
                'Failed to sync page block settings: database error',
                [
                    'pageId' => $pageId,
                    'data'   => $data,
                    'error'  => $exception->getMessage(),
                ],
                __CLASS__,
                __METHOD__
            );
            throw new \Appsolutely\AIO\Exceptions\TransactionException(
                "Failed to sync page block settings for page ID {$pageId}: {$exception->getMessage()}",
                'Unable to save page settings. Please try again.',
                $exception,
                ['pageId' => $pageId]
            );
        } catch (\Exception $exception) {
            log_error(
                'Failed to sync page block settings: unexpected error',
                [
                    'pageId' => $pageId,
                    'data'   => $data,
                    'error'  => $exception->getMessage(),
                ],
                __CLASS__,
                __METHOD__
            );
            throw new \Appsolutely\AIO\Exceptions\TransactionException(
                "Failed to sync page block settings for page ID {$pageId}: {$exception->getMessage()}",
                'Unable to save page settings. Please try again.',
                $exception,
                ['pageId' => $pageId]
            );
        }
    }

    /**
     * Sync a single block setting item
     */
    protected function syncBlockSettingItem(array $blockSetting, int $sort, int $pageId): array|PageBlockSetting
    {
        // Extract required identifiers from block setting data (GrapesJS may nest in attributes)
        $reference      = $blockSetting['reference'] ?? $blockSetting['attributes']['reference'] ?? null;
        $blockReference = $blockSetting['type'] ?? $blockSetting['attributes']['type'] ?? null;
        $blockId        = $this->resolveBlockId($blockReference ?? '');

        // Validate required fields - both block_id and reference are mandatory
        if (empty($blockId) || empty($reference)) {
            log_warning('Invalid block id and reference', [
                'block_id'  => $blockId,
                'reference' => $reference,
            ]);

            return []; // Return empty array to skip this item
        }

        // Resolve view and theme for block value resolution
        $view  = $this->resolveViewFromManifest($blockReference);
        $theme = $this->themeService->resolveThemeName();

        // Check if this block setting already exists for this page and theme
        $found = $this->pageBlockSettingRepository->findBy($pageId, $blockId, $reference, $theme);
        if ($found) {
            // Reactivate, update sort order, and ensure block_value_id matches current theme
            $blockValueId = $this->getBlockValueId($blockId, $theme, $view);
            $this->pageBlockSettingRepository->updateStatusAndSort(
                $found->id,
                Status::ACTIVE->value,
                $sort,
                $blockValueId
            );

            return []; // Return empty array since we updated, not created
        }

        $data = [
            'page_id'        => $pageId,
            'block_id'       => $blockId,
            'block_value_id' => $this->getBlockValueId($blockId, $theme, $view),
            'reference'      => $reference,
            'theme'          => $theme,
            'status'         => Status::ACTIVE->value,
            'sort'           => $sort,
            'published_at'   => now(),
        ];

        return $this->pageBlockSettingRepository->create($data);
    }

    private function resolveBlockId(string $reference): ?int
    {
        if (empty($reference)) {
            return null;
        }

        $blockIdFromCache = Cache::rememberForever(
            "page_block:reference:{$reference}",
            function () use ($reference) {
                $block = $this->pageBlockRepository->findByFieldFirst('reference', $reference);

                return $block?->id;
            }
        );
        if ($blockIdFromCache !== null) {
            return (int) $blockIdFromCache;
        }

        return Cache::rememberForever(
            'page_block:class:general_block',
            function () {
                $block = $this->pageBlockRepository->findByFieldFirst('class', GeneralBlock::class);

                return $block?->id;
            }
        );
    }

    /**
     * Resolve view (template name) from manifest for a block type (manifest template key).
     */
    protected function resolveViewFromManifest(?string $type): string
    {
        if (empty($type)) {
            return '';
        }

        $config = $this->manifestService->getTemplateConfig($type);

        return $config['view'] ?? $type;
    }

    public function getAvailableThemesForSync(int $pageId, string $currentTheme): array
    {
        $currentCount = $this->pageBlockSettingRepository
            ->getActiveSettingsByTheme($pageId, $currentTheme)
            ->count();

        return [
            'has_blocks'   => $currentCount > 0,
            'sync_options' => $currentCount > 0
                ? []
                : $this->pageBlockSettingRepository->getThemesWithBlockCount($pageId, $currentTheme),
        ];
    }

    public function syncFromTheme(int $pageId, string $sourceTheme, string $targetTheme): array
    {
        $sourceSettings = $this->pageBlockSettingRepository
            ->getActiveSettingsByTheme($pageId, $sourceTheme);

        if ($sourceSettings->isEmpty()) {
            return ['synced' => 0, 'skipped' => 0];
        }

        $synced  = 0;
        $skipped = 0;

        $this->db->transaction(function () use ($sourceSettings, $pageId, $targetTheme, &$synced, &$skipped) {
            foreach ($sourceSettings as $sourceSetting) {
                $block     = $sourceSetting->block;
                $blockType = $block?->reference ?? '';

                // Check if this block type exists in the target theme's manifest
                $config = $this->manifestService->getTemplateConfig($blockType, $targetTheme);
                if ($config === null) {
                    $skipped++;

                    continue;
                }

                // Get view name from target theme's manifest (may differ from source)
                $targetView = $config['view'] ?? $blockType;

                // Create block value for target theme with default displayOptions from manifest
                $blockValueId     = $this->getBlockValueId($sourceSetting->block_id, $targetTheme, $targetView);
                $targetBlockValue = $this->pageBlockValueRepository->find($blockValueId);

                // Copy queryOptions from source (theme-independent data like "show 5 articles")
                if ($targetBlockValue !== null) {
                    $sourceQueryOptions = $sourceSetting->blockValue?->query_options;
                    if (! empty($sourceQueryOptions)) {
                        $targetBlockValue->update(['query_options' => $sourceQueryOptions]);
                    }
                }

                // Create block setting for target theme
                $this->pageBlockSettingRepository->create([
                    'page_id'        => $pageId,
                    'block_id'       => $sourceSetting->block_id,
                    'block_value_id' => $blockValueId,
                    'reference'      => $sourceSetting->reference,
                    'theme'          => $targetTheme,
                    'status'         => Status::ACTIVE->value,
                    'sort'           => $sourceSetting->sort,
                    'published_at'   => now(),
                ]);

                $synced++;
            }
        });

        return ['synced' => $synced, 'skipped' => $skipped];
    }

    public function getBlockValueId(int $blockId, ?string $theme = null, string $view = ''): int
    {
        $block = $this->pageBlockRepository->find($blockId);

        // GeneralBlock is used by many templates; each instance gets its own block value
        if ($block !== null && $block->class === GeneralBlock::class) {
            $value = $this->pageBlockValueRepository->create([
                'block_id' => $blockId,
                'theme'    => $theme,
                'view'     => $view,
            ]);

            return $value->id;
        }

        // Try to reuse existing block value for this block and theme
        $existing = $this->pageBlockValueRepository->findByBlockIdAndTheme($blockId, $theme);
        if ($existing !== null) {
            return $existing->id;
        }

        // No existing block value found - create a new one for this theme
        $value = $this->pageBlockValueRepository->create([
            'block_id' => $blockId,
            'theme'    => $theme,
            'view'     => $view,
        ]);

        return $value->id;
    }
}
