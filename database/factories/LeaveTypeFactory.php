<?php

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'type_name' => fake()->randomElement(['Annual', 'Sick', 'Personal', 'Maternity', 'Paternity']) . ' Leave',
            'is_paid' => true,
            'paid_leaves' => fake()->numberBetween(5, 20),
            'carry_forward' => false,
        ];
    }
}
