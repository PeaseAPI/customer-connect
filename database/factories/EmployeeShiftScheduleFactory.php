<?php

namespace Database\Factories;

use App\Models\EmployeeShiftSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeShiftSchedule>
 */
class EmployeeShiftScheduleFactory extends Factory
{
    protected $model = EmployeeShiftSchedule::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'user_id' => \App\Models\User::factory(),
            'shift_id' => \App\Models\Shift::factory(),
            'date' => fake()->date(),
                        'day_of_week' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'shift_type' => 'regular',
        ];
    }
}
