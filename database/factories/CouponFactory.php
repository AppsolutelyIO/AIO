<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\CouponStatus;
use Appsolutely\AIO\Enums\CouponType;
use Appsolutely\AIO\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'                => Str::upper(Str::random(8)),
            'title'               => fake()->sentence(3),
            'description'         => fake()->sentence(),
            'type'                => CouponType::FixedAmount,
            'value'               => fake()->numberBetween(500, 5000),
            'min_order_amount'    => 0,
            'max_discount_amount' => null,
            'usage_limit'         => null,
            'usage_per_user'      => 1,
            'used_count'          => 0,
            'status'              => CouponStatus::Active,
            'starts_at'           => now()->subDay(),
            'expires_at'          => now()->addMonth(),
        ];
    }

    /**
     * Indicate that the coupon is a percentage type.
     */
    public function percentage(int $percent = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type'  => CouponType::Percentage,
            'value' => $percent * CENTS_PER_UNIT,
        ]);
    }

    /**
     * Indicate that the coupon is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => CouponStatus::Expired,
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the coupon is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CouponStatus::Inactive,
        ]);
    }
}
