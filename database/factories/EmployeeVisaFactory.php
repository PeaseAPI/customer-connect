<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeVisaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'user_id' => User::factory(),
            'visa_type' => $this->faker->word(),
        ];
    }
}
