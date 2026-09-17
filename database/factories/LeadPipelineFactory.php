<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\LeadPipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadPipelineFactory extends Factory
{
    protected $model = LeadPipeline::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Sales Pipeline', 'Marketing Pipeline', 'Custom Pipeline']),
            'slug' => fake()->unique()->slug(2),
            'priority' => fake()->numberBetween(1, 10),
            'label_color' => '#' . fake()->hexColor(),
            'default' => fake()->boolean(20),
        ];
    }
}
