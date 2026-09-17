<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'pipeline_stage_id' => PipelineStage::factory(),
            'client_id' => User::factory(),
            'agent_id' => User::factory(),
            'deal_name' => fake()->company() . ' Deal',
            'value' => fake()->randomFloat(2, 1000, 100000),
            'column_priority' => fake()->numberBetween(1, 10),
            'company_name' => fake()->optional()->company(),
            'client_name' => fake()->optional()->name(),
            'client_email' => fake()->optional()->safeEmail(),
            'mobile' => fake()->optional()->phoneNumber(),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
