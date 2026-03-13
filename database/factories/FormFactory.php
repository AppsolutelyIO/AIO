<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Enums\Status;
use Appsolutely\AIO\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name'         => $name,
            'slug'         => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'description'  => fake()->paragraph(),
            'target_table' => null,
            'status'       => Status::ACTIVE,
        ];
    }

    /**
     * Indicate that the form is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::ACTIVE,
        ]);
    }

    /**
     * Indicate that the form is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::INACTIVE,
        ]);
    }

    /**
     * Indicate that the form has a target table.
     */
    public function withTargetTable(string $tableName): static
    {
        return $this->state(fn (array $attributes) => [
            'target_table' => $tableName,
        ]);
    }
}
