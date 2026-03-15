<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\PageBlockValue;

final class PageBlockValueRepository extends BaseRepository
{
    public function model(): string
    {
        return PageBlockValue::class;
    }

    /**
     * Find setting value by block ID
     */
    public function findByBlockId(int $blockId): ?PageBlockValue
    {
        return $this->model->newQuery()
            ->where('block_id', $blockId)
            ->first();
    }

    /**
     * Find block value by block ID and theme.
     * Prefers value with matching theme, then falls back to theme=null.
     */
    public function findByBlockIdAndTheme(int $blockId, ?string $theme): ?PageBlockValue
    {
        $query = $this->model->newQuery()->where('block_id', $blockId);

        if ($theme === null || $theme === '') {
            return $query->whereNull('theme')->first();
        }

        return $this->model->newQuery()
            ->where('block_id', $blockId)
            ->where(function ($q) use ($theme) {
                $q->where('theme', $theme)->orWhereNull('theme');
            })
            ->orderByRaw('CASE WHEN theme = ? THEN 0 ELSE 1 END', [$theme])
            ->first();
    }

    /**
     * Create or update setting value for a block
     */
    public function createOrUpdate(int $blockId, array $data): PageBlockValue
    {
        return $this->model->newQuery()
            ->updateOrCreate(
                ['block_id' => $blockId],
                $data
            );
    }

    /**
     * Delete setting value by block ID
     */
    public function deleteByBlockId(int $blockId): bool
    {
        return $this->model->newQuery()
            ->where('block_id', $blockId)
            ->delete();
    }
}
