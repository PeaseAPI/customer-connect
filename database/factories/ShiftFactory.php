<?php

namespace Database\Factories;

use App\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shift>
 */
class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'shift_name' => fake()->randomElement(['Morning', 'Afternoon', 'Night', 'Evening']) . ' Shift',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'half_day_mark_time' => '13:00:00',
            'late_mark_after' => 15,
        ];
    }
}
