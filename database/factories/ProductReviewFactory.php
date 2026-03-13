<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\ReviewStatus;
use Appsolutely\AIO\Models\Product;
use Appsolutely\AIO\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\ProductReview>
 */
class ProductReviewFactory extends Factory
{
    protected $model = ProductReview::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id'    => User::factory(),
            'order_id'   => null,
            'rating'     => fake()->numberBetween(1, 5),
            'title'      => fake()->sentence(4),
            'body'       => fake()->paragraph(),
            'status'     => ReviewStatus::Pending,
        ];
    }

    /**
     * Indicate the review is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Approved,
        ]);
    }

    /**
     * Indicate the review is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReviewStatus::Rejected,
        ]);
    }

    /**
     * Indicate the review is verified (from a confirmed purchase).
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now(),
        ]);
    }
}
