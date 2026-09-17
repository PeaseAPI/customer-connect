<?php

namespace Database\Factories;

use App\Models\EmployeeDocumentExpiry;
use App\Models\EmployeeDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeeDocumentExpiry>
 */
class EmployeeDocumentExpiryFactory extends Factory
{
    protected $model = EmployeeDocumentExpiry::class;

    public function definition(): array
    {
        return [
            'company_id' => 1,
            'employee_document_id' => EmployeeDocument::factory(),
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'notified' => false,
        ];
    }
}
