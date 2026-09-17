<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskCommentFactory extends Factory
{
    protected $model = TaskComment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->paragraph(),
            'added_by' => User::factory(),
        ];
    }
}
