<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMilestone>
 */
class ProjectMilestoneFactory extends Factory
{
    protected $model = ProjectMilestone::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'project_id' => Project::factory(),
            'milestone_title' => fake()->randomElement(['Phase 1', 'Phase 2', 'MVP', 'Beta', 'Launch']),
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->dateTimeBetween('+1 month', '+6 months')?->format('Y-m-d'),
            'cost' => fake()->randomFloat(2, 1000, 100000),
            'status' => 'incomplete',
        ];
    }
}
