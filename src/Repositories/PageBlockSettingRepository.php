<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\BlockScope;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\PageBlockSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
            $query->where(function ($q) use ($theme) {
                $q->where('theme', $theme)->orWhereNull('theme');
            });

            $query->orderByRaw('CASE WHEN theme = ? THEN 0 ELSE 1 END', [$theme]);
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

    public function getActivePublishedSettings(int $pageId, ?Carbon $datetime = null): \Illuminate\Database\Eloquent\Collection
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
     * Get themes with block counts for a page (excluding a given theme).
     *
     * @return array<int, array{theme: string, block_count: int}>
     */
    public function getThemesWithBlockCount(int $pageId, string $excludeTheme): array
    {
        return $this->model->newQuery()
            ->where('page_id', $pageId)
            ->where('theme', '!=', $excludeTheme)
            ->whereNotNull('theme')
            ->status()
            ->selectRaw('theme, count(*) as block_count')
            ->groupBy('theme')
            ->get()
            ->map(fn ($row) => ['theme' => $row->theme, 'block_count' => $row->block_count])
            ->values()
            ->toArray();
    }

    /**
     * Get active block settings for a page and theme, with block and blockValue loaded.
     */
    public function getActiveSettingsByTheme(int $pageId, string $theme): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->newQuery()
            ->where('page_id', $pageId)
            ->where(function ($q) use ($theme) {
                $q->where('theme', $theme)->orWhereNull('theme');
            })
            ->status()
            ->whereNotNull('sort')
            ->orderByRaw('CASE WHEN theme = ? THEN 0 ELSE 1 END', [$theme])
            ->orderBy('sort')
            ->with(['block', 'blockValue'])
            ->get();
    }

    /**
     * Get block IDs for global blocks that are active and have sort order
     */
    public function getGlobalBlockIds(): Collection
    {
        return $this->model->newQuery()
            ->whereHas('block', function ($query) {
                $query->where('scope', BlockScope::Global->value)->status();
            })
            ->status()
            ->orderBy('sort')
            ->pluck('block_id')
            ->unique();
    }

    /**
     * Find the first active block setting whose blockValue has a force-redirect
     * matching the given redirect_url slug.
     *
     * @param  array<string>  $slugVariants  Possible redirect_url values (with/without leading slash)
     */
    public function findByForceRedirectUrl(array $slugVariants): ?PageBlockSetting
    {
        /** @var PageBlockSetting|null */
        return $this->model->newQuery()
            ->whereHas('blockValue', function ($q) use ($slugVariants) {
                $q->where('display_options->redirect', 'force')
                    ->where(function ($q2) use ($slugVariants) {
                        foreach ($slugVariants as $variant) {
                            $q2->orWhere('display_options->redirect_url', $variant);
                        }
                    });
            })
            ->where('status', Status::ACTIVE)
            ->with('page')
            ->first();
    }
}
