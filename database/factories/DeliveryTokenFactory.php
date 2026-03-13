<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\DeliveryTokenStatus;
use Appsolutely\AIO\Enums\ProductType;
use Appsolutely\AIO\Models\DeliveryToken;
use Appsolutely\AIO\Models\Order;
use Appsolutely\AIO\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\DeliveryToken>
 */
class DeliveryTokenFactory extends Factory
{
    protected $model = DeliveryToken::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = Order::factory()->create();

        return [
            'order_id'      => $order->id,
            'order_item_id' => OrderItem::factory()->create(['order_id' => $order->id])->id,
            'token'         => Str::random(64),
            'product_type'  => ProductType::AutoVirtual,
            'status'        => DeliveryTokenStatus::Pending,
            'expires_at'    => now()->addDays(7),
        ];
    }

    /**
     * Indicate the token has been delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => DeliveryTokenStatus::Delivered,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Indicate the token has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'     => DeliveryTokenStatus::Expired,
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate a manual virtual product.
     */
    public function manualVirtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => ProductType::ManualVirtual,
        ]);
    }
}
