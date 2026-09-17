<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\TaskCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskCategoryFactory extends Factory
{
    protected $model = TaskCategory::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_name' => fake()->randomElement(['Bug', 'Feature', 'Improvement', 'Documentation', 'Testing', 'DevOps']),
            'color' => '#' . fake()->hexColor(),
        ];
    }
}
