<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ImportService
{
    /**
     * Module → Model mapping for import
     */
    protected array $moduleMap = [
        'employees' => \App\Models\User::class,
        'clients' => \App\Models\Client::class,
        'leads' => \App\Models\Lead::class,
        'products' => \App\Models\Product::class,
        'invoices' => \App\Models\Invoice::class,
        'expenses' => \App\Models\Expense::class,
        'tasks' => \App\Models\Task::class,
        'projects' => \App\Models\Project::class,
    ];

    /**
     * Import data from uploaded file into the specified module
     */
    public function import(string $module, $file): array
    {
        if (!isset($this->moduleMap[$module])) {
            throw new \InvalidArgumentException("Unknown import module: {$module}");
        }

        $modelClass = $this->moduleMap[$module];
        $companyId = app(ContextService::class)->getCompanyId();

        // Read CSV/Excel data
        $extension = $file->getClientOriginalExtension();
        if ($extension === 'csv') {
            $data = $this->readCsv($file);
        } else {
            $data = $this->readExcel($file);
        }

        $imported = 0;
        $errors = [];

        foreach ($data as $row) {
            try {
                $row['company_id'] = $companyId;
                $modelClass::create($row);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = $e->getMessage();
            }
        }

        // Log the import
        ActivityLog::create([
            'company_id' => $companyId,
            'user_id' => Auth::id(),
            'subject_type' => $modelClass,
            'description' => "Imported {$imported} {$module} records",
            'properties' => ['errors' => $errors],
        ]);

        return [
            'module' => $module,
            'imported' => $imported,
            'total' => count($data),
            'errors' => $errors,
        ];
    }

    protected function readCsv($file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $headers = fgetcsv($handle);
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_combine($headers, $row);
        }
        fclose($handle);
        return $rows;
    }

    protected function readExcel($file): array
    {
        // Use Laravel Excel if available
        if (class_exists(Excel::class)) {
            return Excel::toArray(new \Maatwebsite\Excel\Imports\HeadingsImport, $file)[0] ?? [];
        }
        return [];
    }
}
