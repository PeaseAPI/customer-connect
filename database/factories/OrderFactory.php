<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => User::factory(),
            'order_number' => 'ORD-' . str_pad(fake()->unique()->numberBetween(1, 9999), 6, '0', STR_PAD_LEFT),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'sub_total' => fake()->randomFloat(2, 100, 50000),
            'discount' => fake()->randomFloat(2, 0, 500),
            'discount_type' => fake()->randomElement(['percent', 'fixed']),
            'total' => fake()->randomFloat(2, 100, 50000),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
