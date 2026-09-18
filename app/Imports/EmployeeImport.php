<?php

namespace App\Imports;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\EmployeeDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeImport implements ToCollection, WithHeadingRow
{
    protected int $importedCount = 0;
    protected int $failedCount = 0;
    protected array $errors = [];

    public function __construct(protected int $companyId) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            try {
                $validator = Validator::make($row->toArray(), [
                    'name' => 'required|string|max:191',
                    'email' => 'required|email|unique:users,email',
                ]);

                if ($validator->fails()) {
                    $this->failedCount++;
                    $this->errors[] = "No." . ($index + 2) . "Row: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'mobile' => $row['mobile'] ?? $row['Phone number'] ?? null,
                    'password' => Str::random(12),
                    'status' => UserStatus::Active,
                    'company_id' => $this->companyId,
                ]);

                $user->employeeDetail()->create([
                    'department_id' => $row['department_id'] ?? $row['DepartmentID'] ?? null,
                    'designation_id' => $row['designation_id'] ?? $row['PositionID'] ?? null,
                    'joining_date' => $row['joining_date'] ?? $row['Join Date'] ?? null,
                    'salary' => $row['salary'] ?? $row['salary'] ?? null,
                    'hourly_rate' => $row['hourly_rate'] ?? $row['Hourly rate'] ?? null,
                    'date_of_birth' => $row['date_of_birth'] ?? $row['Date of Birth'] ?? null,
                    'gender' => $row['gender'] ?? $row['Gender'] ?? null,
                    'address' => $row['address'] ?? $row['Address'] ?? null,
                ]);

                $user->assignRole('employee');
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->failedCount++;
                $this->errors[] = "No." . ($index + 2) . "Row: " . $e->getMessage();
            }
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
