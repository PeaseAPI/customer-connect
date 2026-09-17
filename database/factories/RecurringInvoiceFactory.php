<?php

namespace Database\Factories;

use App\Models\RecurringInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringInvoice>
 */
class RecurringInvoiceFactory extends Factory
{
    protected $model = RecurringInvoice::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'client_id' => \App\Models\User::factory(),
            'invoice_number' => 'RI-' . fake()->unique()->randomNumber(5),
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->dateTimeBetween('+1 month', '+1 year')?->format('Y-m-d'),
            'status' => 'active',
            'sub_total' => fake()->randomFloat(2, 100, 10000),
            'total' => fake()->randomFloat(2, 100, 10000),
        ];
    }
}
