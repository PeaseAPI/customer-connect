<?php

namespace Database\Factories;

use App\Models\ContractSignature;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractSignatureFactory extends Factory
{
    protected $model = ContractSignature::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'contract_id' => Contract::factory(),
            'signed_by' => User::factory(),
            'signature' => $this->faker->text(100),
            'signed_date' => now()->toDateString(),
        ];
    }
}
