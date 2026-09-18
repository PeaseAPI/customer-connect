<?php

namespace App\Imports;

use App\Models\Client;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class ClientImport implements ToCollection, WithHeadingRow
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
                    '客户名称' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    $this->failedCount++;
                    $this->errors[] = "第" . ($index + 2) . "行: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Client::create([
                    'name' => $row['客户名称'],
                    'industry' => $row['行业'] ?? null,
                    'contact_name' => $row['联系人'] ?? null,
                    'contact_phone' => $row['联系电话'] ?? null,
                    'contact_email' => $row['Email'] ?? null,
                    'address' => $row['地址'] ?? null,
                    'company_id' => $this->companyId,
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
