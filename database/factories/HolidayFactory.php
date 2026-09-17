<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HolidayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => null,
            'holiday_name' => $this->faker->words(2, true),
            'date' => $this->faker->date(),
        ];
    }
}
