<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => User::factory(),
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 100, 50000),
            'gateway' => fake()->randomElement(['alipay', 'wechat', 'stripe', 'paypal', 'offline']),
            'paid_on' => fake()->dateTimeBetween('-1 month', 'now'),
            'status' => fake()->randomElement(['pending', 'completed', 'failed']),
        ];
    }
}
