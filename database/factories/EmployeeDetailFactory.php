<?php

namespace Database\Factories;

use App\Models\EmployeeDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDetail>
 */
class EmployeeDetailFactory extends Factory
{
    protected $model = EmployeeDetail::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'user_id' => User::factory(),
            'department_id' => 1,
            'designation_id' => 1,
            'joining_date' => now()->subMonths(6)->toDateString(),
            'salary' => fake()->randomFloat(2, 30000, 150000),
        ];
    }
}
