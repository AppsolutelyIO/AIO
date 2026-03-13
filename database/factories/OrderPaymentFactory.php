<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\OrderPaymentStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\OrderPayment>
 */
class OrderPaymentFactory extends Factory
{
    protected $model = OrderPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference'         => (string) Str::ulid(),
            'order_id'          => Order::factory(),
            'payment_id'        => Payment::factory(),
            'vendor'            => fake()->randomElement(['stripe', 'paypal']),
            'vendor_reference'  => (string) Str::ulid(),
            'vendor_extra_info' => null,
            'payment_amount'    => fake()->numberBetween(10000, 100000),
            'status'            => OrderPaymentStatus::Pending,
        ];
    }

    /**
     * Indicate that the payment is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderPaymentStatus::Paid,
        ]);
    }
}
