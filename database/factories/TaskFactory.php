<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'title' => $this->faker->sentence(4),
            'status' => 'pending',
            'priority' => 'medium',
        ];
    }
}
