<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProposalFactory extends Factory
{
    protected $model = Proposal::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'client_id' => User::factory(),
            'proposal_number' => 'PROP-' . str_pad(fake()->unique()->numberBetween(1, 9999), 6, '0', STR_PAD_LEFT),
            'hash' => Str::uuid()->toString(),
            'subject' => fake()->sentence(3),
            'status' => fake()->randomElement(['draft', 'sent', 'accepted', 'declined', 'expired']),
            'sub_total' => fake()->randomFloat(2, 100, 10000),
            'discount' => fake()->randomFloat(2, 0, 500),
            'discount_type' => fake()->randomElement(['percent', 'fixed']),
            'total' => fake()->randomFloat(2, 100, 10000),
            'valid_till' => fake()->dateTimeBetween('now', '+1 month'),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
