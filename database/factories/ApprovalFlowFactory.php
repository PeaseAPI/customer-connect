<?php

namespace Database\Factories;

use App\Models\ApprovalFlow;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalFlowFactory extends Factory
{
    protected $model = ApprovalFlow::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Leave Approval', 'Expense Approval', 'Contract Approval']),
            'type' => fake()->randomElement(['sequential', 'parallel', 'any']),
            'config' => ['steps' => [['approver_type' => 'role', 'approver_id' => 'admin']]],
            'external_type' => fake()->randomElement(['leave', 'expense', 'contract']),
        ];
    }
}

