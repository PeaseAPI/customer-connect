<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseRecurring;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseRecurring>
 */
class ExpenseRecurringFactory extends Factory
{
    protected $model = ExpenseRecurring::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'expense_id' => Expense::factory(),
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'interval' => 1,
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->dateTimeBetween('+1 month', '+1 year')?->format('Y-m-d'),
            'next_expense_date' => fake()->optional()->dateTimeBetween('+1 day', '+1 month')?->format('Y-m-d'),
            'status' => 'active',
        ];
    }
}
