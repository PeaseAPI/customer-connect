<?php

namespace Database\Factories;

use App\Models\ClientDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientDocumentFactory extends Factory
{
    protected $model = ClientDocument::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'client_id' => User::factory(),
            'document_name' => $this->faker->word() . '.pdf',
            'document_type' => 'pdf',
            'filename' => $this->faker->word() . '.pdf',
            'hashname' => md5($this->faker->word()),
            'size' => $this->faker->numberBetween(1000, 5000000),
            'external_link' => null,
            'added_by' => User::factory(),
        ];
    }
}
