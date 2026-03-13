<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\ShippingRateType;
use Appsolutely\AIO\Models\ShippingRate;
use Appsolutely\AIO\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\ShippingRate>
 */
class ShippingRateFactory extends Factory
{
    protected $model = ShippingRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_zone_id'   => ShippingZone::factory(),
            'name'               => 'Standard Shipping',
            'type'               => ShippingRateType::FlatRate,
            'price'              => fake()->numberBetween(500, 5000),
            'min_order_amount'   => 0,
            'max_order_amount'   => null,
            'min_weight'         => null,
            'max_weight'         => null,
            'estimated_days_min' => 3,
            'estimated_days_max' => 7,
            'is_active'          => true,
        ];
    }

    /**
     * Indicate free shipping.
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'name'  => 'Free Shipping',
            'type'  => ShippingRateType::FreeShipping,
            'price' => 0,
        ]);
    }
}
