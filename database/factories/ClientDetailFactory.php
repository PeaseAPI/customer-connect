<?php

namespace Database\Factories;

use App\Models\ClientDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientDetailFactory extends Factory
{
    protected $model = ClientDetail::class;

    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company(),
            'address' => $this->faker->address(),
        ];
    }
}
