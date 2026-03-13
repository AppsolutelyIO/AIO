<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\CartStatus;
use Appsolutely\AIO\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Cart>
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'session_id'   => null,
            'status'       => CartStatus::Active,
            'total_amount' => 0,
            'metadata'     => null,
            'converted_at' => null,
        ];
    }

    /**
     * Indicate that the cart belongs to a guest (session-based).
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id'    => null,
            'session_id' => fake()->uuid(),
        ]);
    }

    /**
     * Indicate that the cart has been converted to an order.
     */
    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => CartStatus::Converted,
            'converted_at' => now(),
        ]);
    }

    /**
     * Indicate that the cart has been abandoned.
     */
    public function abandoned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CartStatus::Abandoned,
        ]);
    }
}
