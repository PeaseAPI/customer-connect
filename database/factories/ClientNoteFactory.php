<?php

namespace Database\Factories;

use App\Models\ClientNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientNoteFactory extends Factory
{
    protected $model = ClientNote::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'client_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'type' => $this->faker->boolean(),
            'details' => $this->faker->paragraph(),
            'is_client_show' => $this->faker->boolean(),
            'ask_password' => false,
            'added_by' => User::factory(),
        ];
    }
}
