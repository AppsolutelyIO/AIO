<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Stub TeamFactory for package testing.
 *
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'user_id'       => 1,
            'name'          => fake()->words(2, true),
            'personal_team' => false,
            'reference'     => null,
        ];
    }
}
