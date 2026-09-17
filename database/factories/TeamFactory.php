<?php

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'team_name' => fake()->unique()->randomElement(['Alpha', 'Beta', 'Gamma', 'Delta', 'Omega']) . ' Team ' . fake()->randomNumber(2),
        ];
    }
}
