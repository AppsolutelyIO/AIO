<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Models\NotificationSender;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\NotificationSender>
 */
class NotificationSenderFactory extends Factory
{
    protected $model = NotificationSender::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'         => fake()->company(),
            'slug'         => fake()->unique()->slug(),
            'type'         => 'smtp',
            'smtp_host'    => 'smtp.example.com',
            'smtp_port'    => 587,
            'from_address' => fake()->safeEmail(),
            'from_name'    => fake()->name(),
            'category'     => 'external',
            'is_default'   => false,
            'priority'     => 0,
            'is_active'    => true,
        ];
    }

    /**
     * Indicate that the sender is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the sender is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the sender is the default for its category.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
