<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $summary = fake()->sentence();

        return [
            'user_id'           => Model::userModel()::factory(),
            'reference'         => (string) Str::ulid(),
            'summary'           => $summary,
            'amount'            => fake()->numberBetween(10000, 100000),
            'discounted_amount' => 0,
            'total_amount'      => fake()->numberBetween(10000, 100000),
            'status'            => OrderStatus::Pending->value,
            'delivery_info'     => null,
            'note'              => fake()->paragraph(),
            'remark'            => null,
            'ip'                => fake()->ipv4(),
            'request'           => [],
        ];
    }

    /**
     * Indicate that the order is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Pending->value,
        ]);
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed->value,
        ]);
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid->value,
        ]);
    }

    /**
     * Indicate that the order is shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Shipped->value,
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled->value,
        ]);
    }
}
