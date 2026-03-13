<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Livewire;

use Appsolutely\AIO\Enums\PageType;
use Appsolutely\AIO\Repositories\ArticleRepository;
use Illuminate\Contracts\Container\Container;
use Livewire\WithPagination;

final class ArticleList extends GeneralBlock
{
    use WithPagination;

    protected array $defaultQueryOptions = [
        'posts_per_page'  => 6,
        'category_filter' => '', // empty for all categories
        'tag_filter'      => '', // empty for all tags
        'order_by'        => 'published_at', // published_at, title, created_at
        'order_direction' => 'desc', // asc, desc
    ];

    protected function getExtraData(): array
    {
        return [
            'articles' => $this->loadArticles(),
        ];
    }

    protected function loadArticles()
    {
        if (isset($this->page['type']) && $this->page['type'] === PageType::Nested->value) {
            return collect();
        }

        // Resolve ArticleRepository from container (Livewire doesn't support constructor injection)
        $articleRepository = app(ArticleRepository::class);
        $query             = $articleRepository->getPublishedArticles($this->queryOptions);

        return $query->paginate($this->queryOptions['posts_per_page']);
    }
}
