<?php

namespace Database\Factories;

use App\Models\CustomField;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'field_name' => $this->faker->unique()->word(),
            'field_type' => $this->faker->randomElement(['text', 'number', 'date', 'select', 'checkbox']),
            'field_options' => null,
            'module' => $this->faker->randomElement(['tasks', 'projects', 'leads', 'employees', 'clients']),
            'is_required' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}
