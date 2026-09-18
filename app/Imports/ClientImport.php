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
                    'Client Name' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    $this->failedCount++;
                    $this->errors[] = "No." . ($index + 2) . "Row: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                Client::create([
                    'name' => $row['Client Name'],
                    'industry' => $row['Industry'] ?? null,
                    'contact_name' => $row['Contact'] ?? null,
                    'contact_phone' => $row['Contact Phone'] ?? null,
                    'contact_email' => $row['Email'] ?? null,
                    'address' => $row['Address'] ?? null,
                    'company_id' => $this->companyId,
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
