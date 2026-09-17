<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'user_id' => User::factory(),
            'promotion_title' => $this->faker->sentence(3),
            'promotion_date' => now()->toDateString(),
        ];
    }
}
