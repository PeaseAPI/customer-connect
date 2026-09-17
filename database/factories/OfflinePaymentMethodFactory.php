<?php

namespace Database\Factories;

use App\Models\OfflinePaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfflinePaymentMethod>
 */
class OfflinePaymentMethodFactory extends Factory
{
    protected $model = OfflinePaymentMethod::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'method_name' => fake()->randomElement(['Bank Transfer', 'Cash', 'Cheque', 'Wire Transfer']) . ' ' . fake()->randomNumber(2),
            'description' => fake()->optional()->sentence(),
            'bank_name' => fake()->optional()->company(),
            'bank_account_number' => fake()->optional()->numerify('################'),
            'bank_code' => fake()->optional()->swiftBicNumber(),
            'is_active' => true,
        ];
    }
}
