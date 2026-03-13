<?php

declare(strict_types=1);

namespace Appsolutely\AIO\Database\Factories;

use Appsolutely\AIO\Models\Form;
use Appsolutely\AIO\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Appsolutely\AIO\Models\FormField>
 */
class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id'     => Form::factory(),
            'label'       => fake()->words(2, true),
            'name'        => fake()->word(),
            'type'        => fake()->randomElement(['text', 'email', 'textarea', 'select', 'checkbox']),
            'placeholder' => fake()->sentence(),
            'required'    => fake()->boolean(),
            'options'     => null,
            'sort'        => fake()->numberBetween(0, 100),
            'setting'     => [],
        ];
    }
}
