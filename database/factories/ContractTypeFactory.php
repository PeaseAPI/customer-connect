<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContractTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'name' => $this->faker->unique()->word(),
        ];
    }
}
