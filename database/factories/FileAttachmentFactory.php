<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\File;
use Appsolutely\AIO\Models\FileAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\FileAttachment>
 */
class FileAttachmentFactory extends Factory
{
    protected $model = FileAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_id'      => File::factory(),
            'type'         => fake()->randomElement(['thumbnail', 'cover', 'gallery', 'logo']),
            'file_path'    => 'products/' . fake()->slug() . '/thumbnail.jpg',
            'title'        => fake()->sentence(3),
            'keyword'      => fake()->words(3, true),
            'description'  => fake()->sentence(),
            'config'       => null,
            'status'       => Status::ACTIVE,
            'sort_order'   => 0,
            'published_at' => now()->subDay(),
            'expired_at'   => null,
        ];
    }

    /**
     * Indicate that the attachment has an optimized version.
     */
    public function optimized(): static
    {
        return $this->state(fn (array $attributes) => [
            'optimized_path'   => 'optimized/' . fake()->slug() . '.webp',
            'optimized_format' => 'webp',
            'optimized_size'   => fake()->numberBetween(1024, 524288),
            'optimized_width'  => 1920,
            'optimized_height' => 1080,
        ]);
    }
}
