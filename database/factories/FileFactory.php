<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['jpg', 'png', 'gif', 'webp', 'pdf', 'txt']);
        $filename  = Str::uuid() . '.' . $extension;

        return [
            'original_filename' => fake()->word() . '.' . $extension,
            'filename'          => $filename,
            'extension'         => $extension,
            'mime_type'         => $this->getMimeType($extension),
            'path'              => now()->format('Y/m'),
            'size'              => fake()->numberBetween(1024, 10485760),
            'hash'              => hash('sha256', Str::random(40)),
            'width'             => null,
            'height'            => null,
            'disk'              => 's3',
            'metadata'          => null,
        ];
    }

    /**
     * Indicate that the file is an image with dimensions.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'width'     => fake()->numberBetween(100, 4000),
            'height'    => fake()->numberBetween(100, 3000),
        ]);
    }

    /**
     * Indicate that the file is a PNG image.
     */
    public function png(): static
    {
        return $this->state(fn (array $attributes) => [
            'extension' => 'png',
            'mime_type' => 'image/png',
            'width'     => fake()->numberBetween(100, 4000),
            'height'    => fake()->numberBetween(100, 3000),
        ]);
    }

    /**
     * Indicate that the file is a PDF document.
     */
    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'width'     => null,
            'height'    => null,
        ]);
    }

    /**
     * Get MIME type for a given extension.
     */
    private function getMimeType(string $extension): string
    {
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'   => 'image/png',
            'gif'   => 'image/gif',
            'webp'  => 'image/webp',
            'svg'   => 'image/svg+xml',
            'pdf'   => 'application/pdf',
            'txt'   => 'text/plain',
            default => 'application/octet-stream',
        };
    }
}
