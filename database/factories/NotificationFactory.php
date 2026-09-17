<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['system', 'task', 'leave', 'approval', 'mention']),
            'title' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'data' => ['id' => fake()->numberBetween(1, 100)],
            'channel' => fake()->randomElement(['database', 'mail', 'sms']),
        ];
    }
}

