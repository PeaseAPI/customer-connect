<?php

namespace Database\Factories;

use App\Models\Award;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Award>
 */
class AwardFactory extends Factory
{
    protected $model = Award::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Employee of the Month', 'Best Performer', 'Innovation Award', 'Team Player']),
            'description' => fake()->optional()->sentence(),
            'award_date' => fake()->date(),
        ];
    }
}
