<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\OrderShipmentStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderShipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\OrderShipment>
 */
class OrderShipmentFactory extends Factory
{
    protected $model = OrderShipment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'           => Order::factory(),
            'product_type'       => 'PHYSICAL',
            'email'              => fake()->email(),
            'name'               => fake()->name(),
            'mobile'             => fake()->phoneNumber(),
            'address'            => fake()->streetAddress(),
            'city'               => fake()->city(),
            'province'           => fake()->state(),
            'postcode'           => fake()->postcode(),
            'country'            => fake()->countryCode(),
            'delivery_vendor'    => null,
            'delivery_reference' => null,
            'status'             => OrderShipmentStatus::Pending,
        ];
    }

    /**
     * Indicate that the shipment has been shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'             => OrderShipmentStatus::Shipped,
            'delivery_vendor'    => 'ups',
            'delivery_reference' => fake()->uuid(),
        ]);
    }

    /**
     * Indicate that the shipment has been delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'             => OrderShipmentStatus::Delivered,
            'delivery_vendor'    => 'ups',
            'delivery_reference' => fake()->uuid(),
        ]);
    }
}
