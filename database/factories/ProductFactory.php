<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'type'               => ProductType::Physical->value,
            'shipment_methods'   => null,
            'slug'               => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'title'              => $title,
            'subtitle'           => fake()->sentence(),
            'cover'              => null,
            'keywords'           => fake()->words(5, true),
            'description'        => Str::limit(fake()->sentence(20), 255),
            'content'            => fake()->paragraphs(3, true),
            'original_price'     => fake()->numberBetween(10000, 100000),
            'price'              => fake()->numberBetween(5000, 50000),
            'setting'            => [],
            'payment_methods'    => null,
            'additional_columns' => null,
            'sort'               => fake()->numberBetween(0, 100),
            'status'             => Status::ACTIVE,
            'published_at'       => now()->subDay(),
            'expired_at'         => null,
        ];
    }

    /**
     * Indicate that the product is published.
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
     * Indicate that the product is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::INACTIVE,
        ]);
    }

    /**
     * Indicate that the product is a virtual product.
     */
    public function virtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'             => ProductType::AutoVirtual->value,
            'shipment_methods' => Product::SHIPMENT_METHOD_AUTO_DELIVERABLE_VIRTUAL_PRODUCT,
        ]);
    }
}
