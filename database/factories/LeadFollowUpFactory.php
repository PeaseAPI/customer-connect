<?php

namespace Database\Factories;

use App\Models\LeadFollowUp;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFollowUpFactory extends Factory
{
    protected $model = LeadFollowUp::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'lead_id' => Lead::factory(),
            'follow_up_date' => $this->faker->date(),
            'follow_up_type' => $this->faker->randomElement(['call', 'email', 'meeting', 'other']),
            'remark' => $this->faker->sentence(),
            'next_follow_up' => $this->faker->date(),
            'added_by' => User::factory(),
        ];
    }
}
