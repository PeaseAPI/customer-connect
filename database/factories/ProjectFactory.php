<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'project_name' => fake()->randomElement(['Website Redesign', 'ERP Development', 'Mobile App', 'Analytics Platform', 'CRM Integration']) . ' ' . fake()->randomNumber(2),
            'start_date' => fake()->date(),
            'deadline' => fake()->optional()->dateTimeBetween('+1 month', '+1 year')?->format('Y-m-d'),
            'status' => ProjectStatus::InProgress->value,
            'budget' => fake()->optional()->randomFloat(2, 50000, 500000),
        ];
    }
}
