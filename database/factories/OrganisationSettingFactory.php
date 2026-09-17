<?php

namespace Database\Factories;

use App\Models\OrganisationSetting;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationSettingFactory extends Factory
{
    protected $model = OrganisationSetting::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'company_name' => $this->faker->company(),
            'company_email' => $this->faker->companyEmail(),
            'company_phone' => $this->faker->phoneNumber(),
            'timezone' => 'Asia/Shanghai',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
        ];
    }
}
