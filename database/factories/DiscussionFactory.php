<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Discussion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscussionFactory extends Factory
{
    protected $model = Discussion::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'created_by' => User::factory(),
            'is_pinned' => fake()->boolean(20),
            'is_locked' => false,
            'is_announcement' => fake()->boolean(10),
        ];
    }
}
