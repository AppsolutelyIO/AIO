<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Article;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ArticleRepository extends BaseRepository
{
    public function model(): string
    {
        return Article::class;
    }

    public function getPublishedArticles(array $filters = []): Builder
    {
        $query = $this->model->newQuery()
            ->where('status', Status::ACTIVE) // Published articles only
            ->where('published_at', '<=', now());

        // Apply category filter
        if (! empty($filters['category_filter'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('slug', $filters['category_filter']);
            });
        }

        // Apply tag filter
        if (! empty($filters['tag_filter'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->where('slug', $filters['tag_filter']);
            });
        }

        // Apply ordering
        $orderBy        = $filters['order_by'] ?? 'published_at';
        $orderDirection = $filters['order_direction'] ?? 'desc';
        $query->orderBy($orderBy, $orderDirection);

        return $query;
    }

    public function findActiveBySlug(string $slug, ?Carbon $datetime): ?Article
    {
        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Get all published articles for sitemap generation
     */
    public function getPublishedArticlesForSitemap(Carbon $datetime): Collection
    {
        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->with(['categories' => function ($query) {
                $query->status()->published(now())->whereNotNull('slug')->where('slug', '!=', '');
            }])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Find published articles by category slug
     */
    public function findByCategorySlug(string $categorySlug, ?Carbon $datetime = null): Collection
    {
        $datetime = $datetime ?? now();

        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->whereHas('categories', function ($query) use ($categorySlug) {
                $query->where('slug', $categorySlug)->status();
            })
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Get recent published articles
     */
    public function getRecentArticles(int $limit = 10, ?Carbon $datetime = null): Collection
    {
        $datetime = $datetime ?? now();

        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->with(['categories'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get published articles with categories eager loaded
     */
    public function getPublishedWithCategories(?Carbon $datetime = null): Collection
    {
        $datetime = $datetime ?? now();

        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->with(['categories' => function ($query) {
                $query->status()->orderBy('sort');
            }])
            ->orderBy('published_at', 'desc')
            ->get();
    }
}
