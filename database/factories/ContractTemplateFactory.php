<?php

namespace Database\Factories;

use App\Models\ContractTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractTemplateFactory extends Factory
{
    protected $model = ContractTemplate::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'subject' => $this->faker->words(3, true),
            'contract_detail' => $this->faker->paragraphs(3, true),
            'description' => $this->faker->sentence(),
        ];
    }
}
