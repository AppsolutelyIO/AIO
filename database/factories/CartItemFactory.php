<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Models\Cart;
use Appsolutely\AIO\Models\CartItem;
use Appsolutely\AIO\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(1000, 50000);
        $quantity  = fake()->numberBetween(1, 5);

        return [
            'cart_id'        => Cart::factory(),
            'product_id'     => Product::factory(),
            'product_sku_id' => null,
            'quantity'       => $quantity,
            'unit_price'     => $unitPrice,
            'total_price'    => $unitPrice * $quantity,
        ];
    }
}
