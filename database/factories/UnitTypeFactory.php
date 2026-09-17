<?php

namespace Database\Factories;

use App\Models\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UnitType>
 */
class UnitTypeFactory extends Factory
{
    protected $model = UnitType::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'unit_type' => fake()->unique()->randomElement(['kilogram', 'gram', 'liter', 'meter', 'piece', 'box', 'dozen']) . fake()->randomNumber(2),
        ];
    }
}
