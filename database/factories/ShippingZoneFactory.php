<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\ShippingZone>
 */
class ShippingZoneFactory extends Factory
{
    protected $model = ShippingZone::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => fake()->word() . ' Zone',
            'countries' => [fake()->countryCode(), fake()->countryCode()],
            'regions'   => null,
            'sort'      => 0,
            'is_active' => true,
        ];
    }

    /**
     * Indicate the zone is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
