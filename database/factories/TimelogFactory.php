<?php

namespace Database\Factories;

use App\Models\Timelog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimelogFactory extends Factory
{
    protected $model = Timelog::class;

    public function definition(): array
    {
        $startTime = $this->faker->dateTimeThisMonth();
        return [
            'company_id' => 1,
            'project_id' => Project::factory(),
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'start_time' => $startTime,
            'end_time' => (clone $startTime)->modify('+2 hours'),
            'total_hours' => 2.00,
            'memo' => $this->faker->sentence(),
        ];
    }
}
