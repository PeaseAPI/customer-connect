<?php

namespace Database\Factories;

use App\Models\LeadContact;
use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadContactFactory extends Factory
{
    protected $model = LeadContact::class;

    public function definition(): array
    {
        return [
            'company_id' => null, // Will be set via Lead or controller
            'lead_id' => Lead::factory(),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'is_primary' => $this->faker->boolean(),
        ];
    }
}
