<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditNoteFactory extends Factory
{
    protected $model = CreditNote::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => User::factory(),
            'invoice_id' => Invoice::factory(),
            'cn_number' => 'CN-' . str_pad(fake()->unique()->numberBetween(1, 9999), 6, '0', STR_PAD_LEFT),
            'issue_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'sub_total' => fake()->randomFloat(2, 100, 50000),
            'discount' => fake()->randomFloat(2, 0, 500),
            'discount_type' => fake()->randomElement(['percent', 'fixed']),
            'total' => fake()->randomFloat(2, 100, 50000),
            'status' => fake()->randomElement(['open', 'closed', 'draft']),
        ];
    }
}
