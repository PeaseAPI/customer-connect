<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'client_id' => User::factory(),
            'subject' => $this->faker->sentence(3),
            'start_date' => now()->toDateString(),
            'status' => 'draft',
            'hash' => md5(uniqid(mt_rand(), true)),
        ];
    }
}

