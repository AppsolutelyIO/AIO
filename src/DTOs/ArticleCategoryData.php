<?php

declare(strict_types=1);

namespace Appsolutely\AIO\DTOs;

use Appsolutely\AIO\Models\ArticleCategory;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

/**
 * Data Transfer Object for ArticleCategory
 */
final class ArticleCategoryData extends Data
{
    public function __construct(
        public ?int $id = null,
        public ?string $title = null,
        public ?string $slug = null,
        public ?string $keywords = null,
        public ?string $description = null,
        public ?string $cover = null,
        public ?int $status = null,
        public ?Carbon $published_at = null,
        public ?Carbon $expired_at = null,
        public ?array $setting = null,
        public ?Carbon $created_at = null,
        public ?Carbon $updated_at = null,
    ) {}

    /**
     * Create ArticleCategoryData from ArticleCategory model
     */
    public static function fromModel(ArticleCategory $category): self
    {
        return new self(
            id: $category->id,
            title: $category->title,
            slug: $category->slug,
            keywords: $category->keywords,
            description: $category->description,
            cover: $category->cover,
            status: $category->status,
            published_at: $category->published_at,
            expired_at: $category->expired_at,
            setting: $category->setting,
            created_at: $category->created_at,
            updated_at: $category->updated_at,
        );
    }
}
