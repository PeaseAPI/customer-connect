<?php

namespace Database\Factories;

use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'leave_date' => now()->toDateString(),
            'duration' => 'full',
            'status' => 'pending',
        ];
    }
}

