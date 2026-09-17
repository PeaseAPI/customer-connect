<?php

namespace Database\Factories;

use App\Models\ApprovalFlow;
use App\Models\ApprovalRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalRequestFactory extends Factory
{
    protected $model = ApprovalRequest::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'flow_id' => ApprovalFlow::factory(),
            'user_id' => User::factory(),
            'form_data' => ['type' => 'leave', 'from' => '2026-09-20', 'to' => '2026-09-22'],
            'status' => 'pending',
            'current_step' => 1,
        ];
    }
}
