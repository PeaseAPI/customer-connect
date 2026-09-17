<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Starter', 'Professional', 'Enterprise']),
            'description' => fake()->optional()->sentence(),
            'is_free' => false,
            'max_employees' => fake()->numberBetween(5, 500),
            'max_storage_mb' => fake()->numberBetween(500, 50000),
            'monthly_price' => fake()->randomFloat(2, 10, 500),
            'annual_price' => fake()->optional()->randomFloat(2, 100, 5000),
            'trial_days' => fake()->numberBetween(0, 30),
            'is_default' => false,
            'is_recommended' => false,
            'sort_order' => 0,
            'status' => 'active',
            'ai_model_tokens' => fake()->numberBetween(0, 100000),
            'modules' => ['hrm', 'pm', 'finance', 'crm'],
        ];
    }
}
