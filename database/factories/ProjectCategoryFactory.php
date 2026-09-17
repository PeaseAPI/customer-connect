<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectCategoryFactory extends Factory
{
    protected $model = ProjectCategory::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'category_name' => fake()->randomElement(['Internal', 'Client', 'R&D', 'Marketing', 'Infrastructure', 'Support']),
            'color' => '#' . fake()->hexColor(),
        ];
    }
}
