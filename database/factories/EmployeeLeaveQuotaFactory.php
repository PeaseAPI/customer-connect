<?php

namespace Database\Factories;

use App\Models\EmployeeLeaveQuota;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeLeaveQuota>
 */
class EmployeeLeaveQuotaFactory extends Factory
{
    protected $model = EmployeeLeaveQuota::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'user_id' => \App\Models\User::factory(),
            'leave_type_id' => \App\Models\LeaveType::factory(),
            'no_of_leaves' => fake()->numberBetween(5, 20),
            'leaves_used' => 0,
            'leaves_remaining' => fake()->numberBetween(5, 20),
        ];
    }
}
