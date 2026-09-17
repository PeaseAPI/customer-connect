<?php

namespace Database\Factories;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class MilestoneFactory extends Factory
{
    protected $model = Milestone::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'project_id' => Project::factory(),
            'milestone_title' => $this->faker->sentence(3),
            'milestone_cost' => $this->faker->randomFloat(2, 100, 10000),
            'status' => 'incomplete',
            'start_date' => $this->faker->date(),
            'deadline' => $this->faker->date(),
        ];
    }
}
