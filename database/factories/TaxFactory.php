<?php

namespace Database\Factories;

use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tax>
 */
class TaxFactory extends Factory
{
    protected $model = Tax::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'tax_name' => fake()->unique()->randomElement(['GST', 'VAT', 'PST', 'HST', 'Sales Tax', 'Service Tax']) . fake()->randomNumber(2),
            'tax_percent' => fake()->randomFloat(2, 1, 30),
            'is_active' => true,
            'include_in_total' => true,
        ];
    }
}
