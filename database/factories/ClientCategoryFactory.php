<?php

namespace Database\Factories;

use App\Models\ClientCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientCategoryFactory extends Factory
{
    protected $model = ClientCategory::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'category_name' => $this->faker->unique()->word(),
        ];
    }
}
