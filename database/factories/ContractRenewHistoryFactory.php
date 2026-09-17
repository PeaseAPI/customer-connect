<?php

namespace Database\Factories;

use App\Models\ContractRenewHistory;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractRenewHistoryFactory extends Factory
{
    protected $model = ContractRenewHistory::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'contract_id' => Contract::factory(),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'note' => $this->faker->sentence(),
            'added_by' => User::factory(),
        ];
    }
}
