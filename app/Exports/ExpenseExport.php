<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldQueue;

class ExpenseExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    protected array $filters;
    protected int $companyId;

    public function __construct(array $filters = [], int $companyId = 0)
    {
        $this->filters = $filters;
        $this->companyId = $companyId;
    }

    public function query()
    {
        $query = Expense::with(['user', 'project', 'category', 'currency']);

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }
        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }
        if (!empty($this->filters['start_date'])) {
            $query->where('purchase_date', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->where('purchase_date', '<=', $this->filters['end_date']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', '费用名称', '金额', '购买日期', '购买来源',
            'Category', 'Project', 'Status', 'Billable', 'Notes', 'Submitted By', 'Created At',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->id,
            $expense->item_name,
            $expense->amount,
            $expense->purchase_date?->format('Y-m-d'),
            $expense->purchase_from,
            $expense->category?->category_name,
            $expense->project?->project_name,
            $expense->status?->value ?? $expense->status,
            $expense->billable ? '是' : '否',
            $expense->note,
            $expense->user?->name,
            $expense->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
