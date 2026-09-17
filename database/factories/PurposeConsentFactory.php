<?php

namespace Database\Factories;

use App\Models\PurposeConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurposeConsent>
 */
class PurposeConsentFactory extends Factory
{
    protected $model = PurposeConsent::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'name' => fake()->randomElement(['Marketing Email', 'Data Processing', 'Analytics', 'Third-party Sharing']) . ' ' . fake()->randomNumber(2),
            'description' => fake()->optional()->sentence(),
            'is_default' => false,
            'is_active' => true,
            'allow_opt_out' => true,
        ];
    }
}
