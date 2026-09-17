<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppreciationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'user_id' => User::factory(),
            'award_id' => null,
            'description' => $this->faker->sentence(),
            'awarded_by' => null,
        ];
    }
}
