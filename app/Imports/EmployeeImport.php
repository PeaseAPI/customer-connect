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
                    $this->errors[] = "第" . ($index + 2) . "行: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'mobile' => $row['mobile'] ?? $row['手机号'] ?? null,
                    'password' => Str::random(12),
                    'status' => UserStatus::Active,
                    'company_id' => $this->companyId,
                ]);

                $user->employeeDetail()->create([
                    'department_id' => $row['department_id'] ?? $row['部门ID'] ?? null,
                    'designation_id' => $row['designation_id'] ?? $row['职位ID'] ?? null,
                    'joining_date' => $row['joining_date'] ?? $row['入职日期'] ?? null,
                    'salary' => $row['salary'] ?? $row['salary'] ?? null,
                    'hourly_rate' => $row['hourly_rate'] ?? $row['时薪'] ?? null,
                    'date_of_birth' => $row['date_of_birth'] ?? $row['出生日期'] ?? null,
                    'gender' => $row['gender'] ?? $row['性别'] ?? null,
                    'address' => $row['address'] ?? $row['地址'] ?? null,
                ]);

                $user->assignRole('employee');
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->failedCount++;
                $this->errors[] = "第" . ($index + 2) . "行: " . $e->getMessage();
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
