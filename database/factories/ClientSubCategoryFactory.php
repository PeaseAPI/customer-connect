<?php

namespace Database\Factories;

use App\Models\ClientSubCategory;
use App\Models\ClientCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientSubCategoryFactory extends Factory
{
    protected $model = ClientSubCategory::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'category_id' => ClientCategory::factory(),
            'category_name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
