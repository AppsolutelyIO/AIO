<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Sluggable
{
    protected static function bootSluggable(): void
    {
        static::saving(function ($model) {
            $slugConfig  = $model->getSlugConfig();
            $sourceField = $slugConfig['source_field'] ?? 'title';
            $slugField   = $slugConfig['slug_field'] ?? 'slug';

            // If slug is empty, generate from source field
            if (empty($model->{$slugField}) && ! empty($model->{$sourceField})) {
                $model->{$slugField} = static::generateUniqueSlug($model);
            }
        });

        static::deleting(function ($model) {
            $slugConfig = $model->getSlugConfig();
            $slugField  = $slugConfig['slug_field'] ?? 'slug';

            // Add random suffix to slug when deleting
            $model->{$slugField} = $model->{$slugField} . '-' . Str::random(5);
            $model->saveQuietly();
        });
    }

    protected static function generateUniqueSlug($model): string
    {
        $slugConfig  = $model->getSlugConfig();
        $sourceField = $slugConfig['source_field'] ?? 'title';
        $slugField   = $slugConfig['slug_field'] ?? 'slug';
        $parentField = $slugConfig['parent_field'] ?? null;
        $suffix      = '-' . Str::random(5);

        $baseSlug = Str::slug($model->{$sourceField});
        $slug     = $baseSlug;

        // Check for duplicates if parent field is specified
        if ($parentField && isset($model->{$parentField})) {
            $query = $model->where($parentField, $model->{$parentField})
                ->where($slugField, $slug);

            if ($model->exists) {
                $query->where($model->getKeyName(), '!=', $model->getKey());
            }

            if ($query->exists()) {
                $slug = $baseSlug . $suffix;
            }
        }

        return $slug;
    }

    /**
     * Get the configuration for slug generation.
     * Override this method in your model to customize the configuration.
     */
    protected function getSlugConfig(): array
    {
        return [
            'source_field' => 'title',      // Field to generate slug from
            'slug_field'   => 'slug',         // Field to store the slug
            'parent_field' => null,         // Field to check uniqueness against (e.g., product_id)
        ];
    }

    /**
     * Scope to find models by slug with flexible matching.
     * Uses getPossibleSlugs() to match various slug formats.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeSlug($query, string $slug)
    {
        $slugConfig = $this->getSlugConfig();
        $slugField  = $slugConfig['slug_field'] ?? 'slug';

        $possibleSlugs = $this->getPossibleSlugs($slug);

        return $query->whereIn($slugField, $possibleSlugs);
    }

    public function getPossibleSlugs(string $slug): array
    {
        $trimmed = normalize_slug($slug, false);

        if ($trimmed === '') {
            return ['', '/'];
        }

        return array_unique([
            $trimmed,
            '/' . $trimmed,
            '/' . $trimmed . '/',
            $trimmed . '/',
        ]);
    }
}
