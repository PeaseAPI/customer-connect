<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubTaskFactory extends Factory
{
    protected $model = SubTask::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'task_id' => Task::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['incomplete', 'complete']),
            'assigned_to' => User::factory(),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'added_by' => User::factory(),
        ];
    }
}
