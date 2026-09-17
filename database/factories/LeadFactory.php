<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'lead_name' => $this->faker->name(),
            'lead_email' => $this->faker->unique()->safeEmail(),
            'lead_mobile' => $this->faker->phoneNumber(),
        ];
    }
}
