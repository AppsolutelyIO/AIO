<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\RefundStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderPayment;
use Appsolutely\AIO\Models\Refund;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Refund>
 */
class RefundFactory extends Factory
{
    protected $model = Refund::class;

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
            'order_payment_id'  => OrderPayment::factory(),
            'user_id'           => User::factory(),
            'amount'            => fake()->numberBetween(1000, 50000),
            'status'            => RefundStatus::Pending,
            'reason'            => fake()->sentence(),
            'admin_note'        => null,
            'vendor_reference'  => null,
            'vendor_extra_info' => null,
            'refunded_at'       => null,
        ];
    }

    /**
     * Indicate that the refund has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RefundStatus::Approved,
        ]);
    }

    /**
     * Indicate that the refund has been completed.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => RefundStatus::Refunded,
            'refunded_at' => now(),
        ]);
    }
}
