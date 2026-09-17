<?php

namespace Database\Factories;

use App\Models\EmployeeDocument;
use App\Models\EmployeeDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
{
    protected $model = EmployeeDocument::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'employee_detail_id' => EmployeeDetail::factory(),
            'document_name' => fake()->words(3, true),
            'document_type' => fake()->randomElement(['passport', 'visa', 'contract', 'certificate', 'other']),
            'expiry_date' => now()->addMonths(6)->toDateString(),
        ];
    }
}
