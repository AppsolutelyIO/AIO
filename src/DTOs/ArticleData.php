<?php

declare(strict_types=1);

namespace Appsolutely\AIO\DTOs;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Article;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

/**
 * Data Transfer Object for Article
 *
 * This class provides a type-safe way to transfer article data between layers
 * of the application, with automatic validation and transformation.
 *
 * @see https://spatie.be/docs/laravel-data
 */
final class ArticleData extends Data
{
    public function __construct(
        public ?int $id = null,
        public ?string $title = null,
        public ?string $slug = null,
        public ?string $keywords = null,
        public ?string $description = null,
        public ?string $content = null,
        public ?string $cover = null,
        public ?int $status = null,
        public ?Carbon $published_at = null,
        public ?Carbon $expired_at = null,
        public ?int $sort = null,
        public ?array $setting = null,
        #[DataCollectionOf(ArticleCategoryData::class)]
        public ?DataCollection $categories = null,
        public ?Carbon $created_at = null,
        public ?Carbon $updated_at = null,
    ) {}

    /**
     * Create ArticleData from Article model
     */
    public static function fromModel(Article $article): self
    {
        return new self(
            id: $article->id,
            title: $article->title,
            slug: $article->slug,
            keywords: $article->keywords,
            description: $article->description,
            content: $article->content,
            cover: $article->cover,
            status: $article->status,
            published_at: $article->published_at,
            expired_at: $article->expired_at,
            sort: $article->sort,
            setting: $article->setting,
            categories: $article->relationLoaded('categories')
                ? ArticleCategoryData::collection($article->categories)
                : null,
            created_at: $article->created_at,
            updated_at: $article->updated_at,
        );
    }

    /**
     * Create ArticleData from array (e.g., from API request)
     */
    public static function fromArray(array $data): self
    {
        return self::from($data);
    }

    /**
     * Convert to array for API responses
     */
    public function toArray(): array
    {
        return parent::toArray();
    }

    /**
     * Check if article is published
     */
    public function isPublished(): bool
    {
        return $this->status === Status::ACTIVE->value
            && $this->published_at !== null
            && $this->published_at->isPast()
            && ($this->expired_at === null || $this->expired_at->isFuture());
    }

    /**
     * Get article URL
     *
     * Override this method or adjust the route name based on your routing structure
     */
    public function getUrl(): ?string
    {
        if (! $this->slug) {
            return null;
        }

        return url("/articles/{$this->slug}");
    }
}
