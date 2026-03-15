<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Page;
use Appsolutely\AIO\Repositories\Traits\Reference;
use Appsolutely\AIO\Repositories\Traits\Status;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class PageRepository extends BaseRepository
{
    use Reference;
    use Status;

    public function model(): string
    {
        return Page::class;
    }

    /**
     * Standard eager loading for pages with sorted, active blocks.
     *
     * @return array<int|string, \Closure|string>
     */
    private function blocksEagerLoad(): array
    {
        return [
            'blocks' => function ($query) {
                $query->status()->whereNotNull('sort')->orderBy('sort');
            },
            'blocks.block',
            'blocks.blockValue',
        ];
    }

    public function findPageBySlug(string $slug, Carbon $datetime): ?Page
    {
        return $this->model->newQuery()
            ->slug($slug)
            ->status()
            ->published($datetime)
            ->with($this->blocksEagerLoad())
            ->first();
    }

    public function findPageById(int $id, Carbon $datetime): ?Page
    {
        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->with($this->blocksEagerLoad())
            ->find($id);
    }

    /**
     * Get all published pages for sitemap generation (only columns needed for sitemap)
     */
    public function getPublishedPagesForSitemap(Carbon $datetime): Collection
    {
        return $this->model->newQuery()
            ->select(['id', 'slug', 'parent_id', 'status', 'published_at', 'expired_at', 'updated_at'])
            ->status()
            ->published($datetime)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Update page setting
     */
    public function updateSetting(int $id, array $setting): Page
    {
        return $this->update(['setting' => $setting], $id);
    }

    /**
     * Find page by slug without datetime filtering (for admin use)
     */
    public function findBySlug(string $slug): ?Page
    {
        return $this->model->newQuery()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get pages by parent ID
     */
    public function getByParentId(?int $parentId, ?Carbon $datetime = null): Collection
    {
        $query = $this->model->newQuery()
            ->where('parent_id', $parentId)
            ->status();

        if ($datetime !== null) {
            $query->published($datetime);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    /**
     * Find page by reference with standard blocks eager loading.
     */
    public function findByReference(string $reference): Page
    {
        return $this->model->newQuery()
            ->where('reference', $reference)
            ->with($this->blocksEagerLoad())
            ->firstOrFail();
    }

    /**
     * Get published pages with blocks eager loaded
     */
    public function getPublishedWithBlocks(?Carbon $datetime = null): Collection
    {
        $datetime = $datetime ?? now();

        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->with($this->blocksEagerLoad())
            ->orderBy('published_at', 'desc')
            ->get();
    }
}
