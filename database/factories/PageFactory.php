<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'reference'       => (string) Str::ulid(),
            'title'           => $title,
            'name'            => $title,
            'slug'            => Str::slug($title),
            'keywords'        => fake()->words(5, true),
            'description'     => fake()->paragraph(),
            'content'         => fake()->paragraphs(3, true),
            'setting'         => [],
            'canonical_url'   => null,
            'meta_robots'     => null,
            'og_title'        => null,
            'og_description'  => null,
            'og_image'        => null,
            'structured_data' => null,
            'hreflang'        => null,
            'language'        => null,
            'parent_id'       => null,
            'published_at'    => now(),
            'expired_at'      => null,
            'status'          => Status::ACTIVE,
        ];
    }

    /**
     * Indicate that the page is published.
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
     * Indicate that the page is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::INACTIVE,
        ]);
    }

    /**
     * Indicate that the page is scheduled for future publication.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => Status::ACTIVE,
            'published_at' => now()->addDay(),
        ]);
    }

    /**
     * Indicate that the page is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDays(10),
            'expired_at'   => now()->subDay(),
        ]);
    }
}
