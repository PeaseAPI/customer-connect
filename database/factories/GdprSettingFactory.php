<?php

namespace Database\Factories;

use App\Models\GdprSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GdprSetting>
 */
class GdprSettingFactory extends Factory
{
    protected $model = GdprSetting::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'gdpr_enable' => false,
            'privacy_policy' => fake()->optional()->paragraph(),
            'terms_and_conditions' => fake()->optional()->paragraph(),
            'show_consent_on_signup' => false,
            'allow_right_to_be_forgotten' => false,
            'allow_data_export' => false,
        ];
    }
}
