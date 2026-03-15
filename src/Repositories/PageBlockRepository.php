<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Models\PageBlock;
use Illuminate\Database\Eloquent\Collection;

final class PageBlockRepository extends BaseRepository
{
    public function model(): string
    {
        return PageBlock::class;
    }

    /**
     * Get all global scope blocks that pages are using
     */
    public function getGlobalBlocks(): Collection
    {
        return $this->model->newQuery()
            ->with(['settings.page'])
            ->whereHas('settings', function ($query) {
                $query->status();
            })
            ->where('scope', BlockScope::Global->value)
            ->status()
            ->orderBy('id')
            ->get();
    }

    /**
     * Get global blocks by IDs with status and sort ordering
     */
    public function getGlobalBlocksByIds(array $blockIds): Collection
    {
        return $this->model->newQuery()
            ->where('scope', BlockScope::Global->value)
            ->whereIn('id', $blockIds)
            ->status()
            ->orderBy('sort')
            ->get();
    }

    /**
     * Get blocks by scope
     */
    public function getBlocksByScope(BlockScope $scope): Collection
    {
        return $this->model->newQuery()
            ->where('scope', $scope->value)
            ->status()
            ->orderBy('sort')
            ->get();
    }

    /**
     * Get all active blocks keyed by class (component FQCN).
     * Used for manifest-to-block matching in block registry.
     *
     * @return array<string, \Appsolutely\AIO\Models\PageBlock>
     */
    public function getBlocksByClass(): array
    {
        $blocks = $this->model->newQuery()
            ->with('group')
            ->status()
            ->orderBy('sort')
            ->get();

        $byClass = [];
        foreach ($blocks as $block) {
            $byClass[$block->class] = $block;
        }

        return $byClass;
    }
}
