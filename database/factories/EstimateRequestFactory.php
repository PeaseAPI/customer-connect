<?php

namespace Database\Factories;

use App\Models\EstimateRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstimateRequest>
 */
class EstimateRequestFactory extends Factory
{
    protected $model = EstimateRequest::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'company_name' => fake()->optional()->company(),
            'requirement' => fake()->optional()->paragraph(),
            'status' => 'pending',
        ];
    }
}
