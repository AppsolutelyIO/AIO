<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\PageBlockSetting;

final class PageBlockSettingRepository extends BaseRepository
{
    public function model(): string
    {
        return PageBlockSetting::class;
    }

    public function findBy(?int $pageId, ?int $blockId, ?string $reference, ?string $theme = null): ?PageBlockSetting
    {
        $query = $this->model->newQuery();
        if (! empty($pageId)) {
            $query->where('page_id', $pageId);
        }

        if (! empty($blockId)) {
            $query->where('block_id', $blockId);
        }

        if (! empty($reference)) {
            $query->where('reference', $reference);
        }

        if ($theme !== null) {
            $query->where('theme', $theme);
        }

        return $query->first();
    }

    public function findByBlockId(int $blockId): ?PageBlockSetting
    {
        return $this->model->newQuery()->where('block_id', $blockId)->status()->first();
    }

    public function resetSetting(int $pageId, ?string $theme = null): int
    {
        $query = $this->model->newQuery()->where('page_id', $pageId);

        if ($theme !== null) {
            $query->where('theme', $theme);
        }

        return $query->update(['status' => Status::INACTIVE, 'sort' => 0]);
    }

    public function getActivePublishedSettings(int $pageId, ?\Carbon\Carbon $datetime = null): \Illuminate\Database\Eloquent\Collection
    {
        $datetime = $datetime ?? now();

        return $this->model->newQuery()
            ->where('page_id', $pageId)
            ->status()
            ->published($datetime)
            ->orderBy('sort')
            ->get();
    }

    public function updatePublishStatus(int $id, ?string $publishedAt = null, ?string $expiredAt = null): int
    {
        $data = [];

        if ($publishedAt !== null) {
            $data['published_at'] = $publishedAt;
        }

        if ($expiredAt !== null) {
            $data['expired_at'] = $expiredAt;
        }

        return $this->model->newQuery()
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Update status, sort, and optionally block_value_id for a page block setting.
     */
    public function updateStatusAndSort(int $id, int $status, int $sort, ?int $blockValueId = null): PageBlockSetting
    {
        $data = [
            'status' => $status,
            'sort'   => $sort,
        ];

        if ($blockValueId !== null) {
            $data['block_value_id'] = $blockValueId;
        }

        return $this->update($data, $id);
    }

    /**
     * Get block IDs for global blocks that are active and have sort order
     */
    public function getGlobalBlockIds(): \Illuminate\Support\Collection
    {
        return $this->model->newQuery()
            ->whereHas('block', function ($query) {
                $query->where('scope', \Appsolutely\AIO\Enums\BlockScope::Global->value)->status();
            })
            ->status()
            ->orderBy('sort')
            ->pluck('block_id')
            ->unique();
    }
}
