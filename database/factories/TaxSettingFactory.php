<?php

namespace Database\Factories;

use App\Models\TaxSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxSettingFactory extends Factory
{
    protected $model = TaxSetting::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'tax_name' => $this->faker->unique()->word() . ' Tax',
            'tax_percent' => $this->faker->randomFloat(2, 1, 25),
            'is_active' => true,
        ];
    }
}
