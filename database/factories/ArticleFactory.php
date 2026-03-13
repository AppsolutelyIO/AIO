<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title'        => $title,
            'slug'         => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'keywords'     => fake()->words(5, true),
            'description'  => Str::limit(fake()->paragraph(), 255),
            'content'      => fake()->paragraphs(5, true),
            'cover'        => null,
            'setting'      => [],
            'status'       => Status::ACTIVE,
            'sort'         => fake()->numberBetween(0, 100),
            'published_at' => now()->subDay(),
            'expired_at'   => null,
        ];
    }

    /**
     * Indicate that the article is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
            'expired_at'   => null,
        ]);
    }

    /**
     * Indicate that the article is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::INACTIVE,
        ]);
    }
}
