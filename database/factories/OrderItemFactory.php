<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\OrderStatus;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;
use Appsolutely\AIO\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price    = fake()->numberBetween(1000, 50000);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'order_id'          => Order::factory(),
            'product_id'        => Product::factory(),
            'product_sku_id'    => 0,
            'reference'         => (string) Str::ulid(),
            'summary'           => fake()->sentence(),
            'original_price'    => $price,
            'price'             => $price,
            'quantity'          => $quantity,
            'discounted_amount' => 0,
            'amount'            => $price * $quantity,
            'product_snapshot'  => [],
            'status'            => OrderStatus::Pending->value,
        ];
    }
}
