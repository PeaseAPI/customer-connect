<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyAddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'address' => $this->faker->address(),
            'is_default' => false,
        ];
    }
}
