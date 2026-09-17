<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('now', '+1 month');
        return [
            'company_id' => null,
            'event_name' => $this->faker->sentence(3),
            'start_date_time' => $start->format('Y-m-d H:i:s'),
            'end_date_time' => (clone $start)->modify('+2 hours')->format('Y-m-d H:i:s'),
            'location' => $this->faker->city(),
        ];
    }
}
