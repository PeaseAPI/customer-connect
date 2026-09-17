<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Estimate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstimateFactory extends Factory
{
    protected $model = Estimate::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => User::factory(),
            'estimate_number' => 'EST-' . str_pad(fake()->unique()->numberBetween(1, 9999), 6, '0', STR_PAD_LEFT),
            'hash' => md5(uniqid(mt_rand(), true)),
            'sub_total' => fake()->randomFloat(2, 100, 50000),
            'discount' => fake()->randomFloat(2, 0, 500),
            'total' => fake()->randomFloat(2, 100, 50000),
            'status' => fake()->randomElement(['pending', 'accepted', 'declined', 'expired']),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'valid_till' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
