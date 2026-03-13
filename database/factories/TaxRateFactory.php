<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\TaxRateType;
use Appsolutely\AIO\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\TaxRate>
 */
class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->word() . ' Tax',
            'country'     => fake()->countryCode(),
            'region'      => null,
            'type'        => TaxRateType::Percentage,
            'rate'        => 1000, // 10% in basis points
            'priority'    => 0,
            'is_compound' => false,
            'is_active'   => true,
        ];
    }

    /**
     * Indicate a fixed amount tax.
     */
    public function fixed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TaxRateType::Fixed,
            'rate' => fake()->numberBetween(100, 500),
        ]);
    }

    /**
     * Indicate a compound tax.
     */
    public function compound(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_compound' => true,
        ]);
    }
}
