<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'category_name' => fake()->randomElement(['Office Supplies', 'Travel', 'Software', 'Equipment', 'Marketing', 'Utilities']) . ' ' . fake()->randomNumber(2),
        ];
    }
}
