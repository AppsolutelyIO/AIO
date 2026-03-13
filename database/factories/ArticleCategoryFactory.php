<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\ArticleCategory>
 */
class ArticleCategoryFactory extends Factory
{
    protected $model = ArticleCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(2, true);

        return [
            'parent_id'    => null,
            'title'        => $title,
            'slug'         => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'keywords'     => fake()->words(5, true),
            'description'  => fake()->paragraph(),
            'cover'        => null,
            'setting'      => [],
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
            'expired_at'   => null,
        ];
    }
}
