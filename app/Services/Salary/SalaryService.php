<?php

namespace App\Services\Salary;

use App\Models\SalaryStructure;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SalaryService
{
    public function listStructures(array $filters = [], int $perPage = 15)
    {
        $query = SalaryStructure::with(['user']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->paginate($perPage);
    }

    public function createStructure(array $data): SalaryStructure
    {
        $data['gross_salary'] = $this->calculateGrossSalary($data);
        $data['net_salary'] = $this->calculateNetSalary($data);
        return SalaryStructure::create($data);
    }

    public function updateStructure(SalaryStructure $structure, array $data): SalaryStructure
    {
        if (isset($data['basic_salary']) || isset($data['allowances']) || isset($data['deductions'])) {
            $merged = array_merge($structure->toArray(), $data);
            $data['gross_salary'] = $this->calculateGrossSalary($merged);
            $data['net_salary'] = $this->calculateNetSalary($merged);
        }

        $structure->update($data);
        return $structure->fresh();
    }

    public function deleteStructure(SalaryStructure $structure): bool
    {
        return $structure->delete();
    }

    public function generatePayslip(int $userId, string $month): Payslip
    {
        return DB::transaction(function () use ($userId, $month) {
            $structure = SalaryStructure::where('user_id', $userId)
                ->where('is_active', true)->first();

            if (!$structure) {
                throw new \Exception('This employee has no active salary structure');
            }

            return Payslip::updateOrCreate(
                ['company_id' => $structure->company_id, 'user_id' => $userId, 'month' => $month],
                [
                    'basic_salary' => $structure->basic_salary,
                    'allowances' => $structure->allowances,
                    'deductions' => $structure->deductions,
                    'gross_salary' => $structure->gross_salary,
                    'tax' => $this->calculateTax($structure->gross_salary),
                    'net_salary' => $structure->net_salary - $this->calculateTax($structure->gross_salary),
                    'status' => 'draft',
                ]
            );
        });
    }

    public function batchGeneratePayslips(string $month, int $companyId): array
    {
        $structures = SalaryStructure::where('company_id', $companyId)
            ->where('is_active', true)->get();

        $generated = 0;
        foreach ($structures as $structure) {
            $this->generatePayslip($structure->user_id, $month);
            $generated++;
        }

        return ['generated' => $generated, 'month' => $month];
    }

    public function sendPayslip(Payslip $payslip): Payslip
    {
        $payslip->update(['status' => 'sent']);
        $payslip->user?->notify(new \App\Notifications\EmployeeReminderNotification('工资条已发送', [
            'payslip_id' => $payslip->id,
            'month' => $payslip->month,
            'net_salary' => (string) $payslip->net_salary,
        ]));

        return $payslip->fresh();
    }

    public function listPayslips(array $filters = [], int $perPage = 15)
    {
        $query = Payslip::with(['user']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    private function calculateGrossSalary(array $data): float
    {
        $basic = (float) ($data['basic_salary'] ?? 0);
        $allowanceTotal = collect($data['allowances'] ?? [])->sum(function ($a) use ($basic) {
            return ($a['type'] ?? 'fixed') === 'percent'
                ? $basic * ($a['amount'] ?? 0) / 100
                : ($a['amount'] ?? 0);
        });
        return round($basic + $allowanceTotal, 2);
    }

    private function calculateNetSalary(array $data): float
    {
        $gross = $this->calculateGrossSalary($data);
        $deductionTotal = collect($data['deductions'] ?? [])->sum(function ($d) use ($gross) {
            return ($d['type'] ?? 'fixed') === 'percent'
                ? $gross * ($d['amount'] ?? 0) / 100
                : ($d['amount'] ?? 0);
        });
        return round($gross - $deductionTotal, 2);
    }

    private function calculateTax(float $grossSalary): float
    {
        // 简化的税率计算，实际应根据China 税法
        if ($grossSalary <= 5000) return 0;
        if ($grossSalary <= 8000) return round(($grossSalary - 5000) * 0.03, 2);
        if ($grossSalary <= 17000) return round(($grossSalary - 5000) * 0.1 - 210, 2);
        if ($grossSalary <= 30000) return round(($grossSalary - 5000) * 0.2 - 1410, 2);
        return round(($grossSalary - 5000) * 0.25 - 2660, 2);
    }
}
