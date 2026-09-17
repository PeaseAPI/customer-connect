<?php

namespace Database\Factories;

use App\Models\EmployeeShiftChangeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeShiftChangeRequest>
 */
class EmployeeShiftChangeRequestFactory extends Factory
{
    protected $model = EmployeeShiftChangeRequest::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'user_id' => \App\Models\User::factory(),
            'shift_id' => \App\Models\Shift::factory(),
            'effective_date' => fake()->date(),
            'reason' => fake()->optional()->sentence(),
            'status' => 'pending',
        ];
    }
}
