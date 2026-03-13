<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ProductCategory;
use Appsolutely\AIO\Repositories\Traits\ActiveTreeList;

final class ProductCategoryRepository extends BaseRepository
{
    use ActiveTreeList;

    public function model(): string
    {
        return ProductCategory::class;
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?ProductCategory
    {
        return $this->model->newQuery()
            ->where('slug', $slug)
            ->status()
            ->first();
    }

    /**
     * Get categories with product count
     */
    public function getWithProductCount(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->newQuery()
            ->status()
            ->withCount('products')
            ->orderBy('sort')
            ->get();
    }
}
