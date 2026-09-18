<?php

namespace App\Imports;

use App\Models\Lead;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class LeadImport implements ToCollection, WithHeadingRow
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
                    'lead_name' => 'required|string|max:191',
                ]);

                if ($validator->fails()) {
                    $this->failedCount++;
                    $this->errors[] = "第" . ($index + 2) . "行: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Lead::create([
                    'lead_name' => $row['lead_name'],
                    'lead_email' => $row['lead_email'] ?? $row['邮箱'] ?? null,
                    'lead_mobile' => $row['lead_mobile'] ?? $row['手机号'] ?? null,
                    'lead_address' => $row['lead_address'] ?? $row['地址'] ?? null,
                    'agent_id' => $row['agent_id'] ?? $row['代理ID'] ?? null,
                    'source_id' => $row['source_id'] ?? $row['来源ID'] ?? null,
                    'status_id' => $row['status_id'] ?? $row['状态ID'] ?? null,
                    'pipeline_stage_id' => $row['pipeline_stage_id'] ?? null,
                    'value' => $row['value'] ?? $row['价值'] ?? null,
                    'next_follow_up' => $row['next_follow_up'] ?? null,
                    'company_id' => $this->companyId,
                    'created_by' => auth()->id(),
                ]);

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
