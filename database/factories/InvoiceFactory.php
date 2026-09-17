<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'client_id' => User::factory(),
            'invoice_number' => 'INV-' . str_pad($this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'sub_total' => $this->faker->randomFloat(2, 100, 10000),
            'total' => $this->faker->randomFloat(2, 100, 10000),
            'date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'status' => 'draft',
            'hash' => md5(uniqid(mt_rand(), true)),
        ];
    }
}

