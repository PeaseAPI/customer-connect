<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'item_name' => fake()->randomElement(['Office Supplies', 'Software License', 'Travel Expense', 'Equipment Purchase', 'Consulting Fee']),
            'purchase_from' => fake()->optional()->company(),
            'purchase_date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'status' => 'pending',
            'billable' => false,
            'note' => fake()->optional()->sentence(),
            'exchange_rate' => 1.0000,
        ];
    }
}
