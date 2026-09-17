<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'package_id' => Package::factory(),
            'status' => 'active',
            'ends_at' => now()->addYear(),
            'trial_ends_at' => null,
        ];
    }
}
