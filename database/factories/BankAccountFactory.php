<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BankAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'bank_name' => $this->faker->company(),
            'account_name' => $this->faker->name(),
            'account_number' => $this->faker->numerify('################'),
            'status' => 'active',
        ];
    }
}
