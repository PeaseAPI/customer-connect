<?php

namespace App\Imports;

use App\Models\Expense;
use App\Enums\ExpenseStatus;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class ExpenseImport implements ToCollection, WithHeadingRow
{
    protected int $importedCount = 0;
    protected int $failedCount = 0;
    protected array $errors = [];

    public function __construct(protected int $companyId, protected int $userId) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            try {
                $validator = Validator::make($row->toArray(), [
                    'item_name' => 'required|string|max:191',
                    'amount' => 'required|numeric',
                    'purchase_date' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $this->failedCount++;
                    $this->errors[] = "No." . ($index + 2) . "Row: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Expense::create([
                    'item_name' => $row['item_name'],
                    'amount' => $row['amount'],
                    'purchase_date' => $row['purchase_date'],
                    'purchase_from' => $row['purchase_from'] ?? null,
                    'note' => $row['note'] ?? $row['Notes'] ?? null,
                    'billable' => !empty($row['billable']) || !empty($row['Billable']),
                    'company_id' => $this->companyId,
                    'user_id' => $this->userId,
                    'created_by' => $this->userId,
                    'status' => ExpenseStatus::Pending,
                ]);

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
