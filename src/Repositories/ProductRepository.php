<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

final class ProductRepository extends BaseRepository
{
    public function model(): string
    {
        return Product::class;
    }

    public function getActiveProducts(): Collection
    {
        return $this->model->status()->orderBy('sort', 'asc')->get();
    }

    /**
     * Get a list of active products for dropdown selection
     */
    public function getActiveList(): array
    {
        return $this->model->status()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->toArray();
    }

    /**
     * Get all published products for sitemap generation
     */
    public function getPublishedProductsForSitemap(Carbon $datetime): Collection
    {
        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->with(['categories' => function ($query) use ($datetime) {
                $query->status()->published($datetime)->whereNotNull('slug')->where('slug', '!=', '');
            }])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Find published products by category slug
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
     * Get recent published products
     */
    public function getRecentProducts(int $limit = 10, ?Carbon $datetime = null): Collection
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
     * Get published products with categories eager loaded
     */
    public function getPublishedWithCategories(?Carbon $datetime = null): Collection
    {
        $datetime = $datetime ?? now();

        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->with(['categories' => function ($query) {
                $query->status()->orderBy('title');
            }])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    /**
     * Find product by slug with categories
     */
    public function findBySlugWithCategories(string $slug, ?Carbon $datetime = null): ?Product
    {
        $datetime = $datetime ?? now();

        return $this->model->newQuery()
            ->status()
            ->published($datetime)
            ->where('slug', $slug)
            ->with(['categories' => function ($query) {
                $query->status()->orderBy('title');
            }])
            ->first();
    }
}
