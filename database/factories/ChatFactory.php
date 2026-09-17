<?php

namespace Database\Factories;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chat>
 */
class ChatFactory extends Factory
{
    protected $model = Chat::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'type' => fake()->randomElement(['private', 'group']),
            'name' => fake()->optional()->word(),
        ];
    }
}
