<?php

namespace Database\Factories;

use App\Models\ContractDiscussion;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractDiscussionFactory extends Factory
{
    protected $model = ContractDiscussion::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'contract_id' => Contract::factory(),
            'comment' => $this->faker->paragraph(),
            'added_by' => User::factory(),
        ];
    }
}
