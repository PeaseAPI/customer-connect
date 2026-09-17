<?php

namespace App\Services\HRM;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\EmployeeDetail;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = User::employees()->with(['employeeDetail.department', 'employeeDetail.designation']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['department_id'])) {
            $query->whereHas('employeeDetail', fn($q) => $q->where('department_id', $filters['department_id']));
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'] ?? null,
                                'password' => $data['password'],
                'status' => UserStatus::Active,
                'company_id' => $data['company_id'],
            ]);

            $user->employeeDetail()->create(
                collect($data)->only([
                    'department_id', 'designation_id', 'joining_date', 'salary',
                    'hourly_rate', 'date_of_birth', 'gender', 'address',
                ])->toArray()
            );

            $user->assignRole('employee');

            return $user;
        });
    }

    public function update(User $employee, array $data): User
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update(collect($data)->only(['name', 'mobile', 'status'])->toArray());

            $detailFields = collect($data)->only([
                'department_id', 'designation_id', 'joining_date', 'salary',
                'hourly_rate', 'date_of_birth', 'gender', 'address',
            ])->filter()->toArray();

            if (!empty($detailFields)) {
                $employee->employeeDetail?->update($detailFields);
            }

            return $employee->fresh();
        });
    }

    public function delete(User $employee): bool
    {
        return $employee->delete();
    }
}
