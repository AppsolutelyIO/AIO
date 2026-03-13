<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\MenuTarget;
use Appsolutely\AIO\Enums\MenuType;
use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'left'           => 0,
            'right'          => 0,
            'parent_id'      => null,
            'title'          => fake()->words(2, true),
            'reference'      => fake()->unique()->slug(2),
            'remark'         => null,
            'url'            => fake()->slug(2),
            'type'           => MenuType::Link,
            'icon'           => null,
            'thumbnail'      => null,
            'setting'        => null,
            'permission_key' => null,
            'target'         => MenuTarget::Self,
            'is_external'    => false,
            'published_at'   => now(),
            'expired_at'     => null,
            'status'         => Status::ACTIVE,
        ];
    }

    /**
     * Indicate that the menu item is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => Status::ACTIVE,
            'published_at' => now()->subDay(),
            'expired_at'   => null,
        ]);
    }

    /**
     * Indicate that the menu item is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::INACTIVE,
        ]);
    }
}
