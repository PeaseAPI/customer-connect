<?php

namespace Database\Factories;

use App\Models\ProjectTimeLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectTimeLog>
 */
class ProjectTimeLogFactory extends Factory
{
    protected $model = ProjectTimeLog::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'project_id' => \App\Models\Project::factory(),
            'user_id' => \App\Models\User::factory(),
            'log_date' => fake()->date(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'total_hours' => 8.0,
            'billable' => true,
        ];
    }
}
