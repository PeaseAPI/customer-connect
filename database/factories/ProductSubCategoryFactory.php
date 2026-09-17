<?php

namespace Database\Factories;

use App\Models\ProductSubCategory;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductSubCategoryFactory extends Factory
{
    protected $model = ProductSubCategory::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'category_id' => ProductCategory::factory(),
            'category_name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
