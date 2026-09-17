<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\LeadPipeline;
use App\Models\PipelineStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'lead_pipeline_id' => LeadPipeline::factory(),
            'name' => fake()->randomElement(['New', 'Contacted', 'Proposal', 'Negotiation', 'Won', 'Lost']),
            'slug' => fake()->unique()->slug(1),
            'type' => fake()->randomElement(['lead', 'won', 'lost']),
            'priority' => fake()->numberBetween(1, 10),
            'label_color' => '#' . fake()->hexColor(),
            'default' => fake()->boolean(20),
        ];
    }
}
