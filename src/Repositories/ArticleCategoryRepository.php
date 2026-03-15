<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Repositories;

use Appsolutely\AIO\Models\ArticleCategory;
use Appsolutely\AIO\Repositories\Traits\ActiveTreeList;
use Illuminate\Database\Eloquent\Collection;

final class ArticleCategoryRepository extends BaseRepository
{
    use ActiveTreeList;

    public function model(): string
    {
        return ArticleCategory::class;
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?ArticleCategory
    {
        return $this->model->newQuery()
            ->where('slug', $slug)
            ->status()
            ->first();
    }

    /**
     * Get categories with article count
     */
    public function getWithArticleCount(): Collection
    {
        return $this->model->newQuery()
            ->status()
            ->withCount('articles')
            ->orderBy('sort')
            ->get();
    }
}
