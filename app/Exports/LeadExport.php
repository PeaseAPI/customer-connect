<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldQueue;

class LeadExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Lead::with(['owner', 'source']);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['owner_id'])) {
            $query->where('owner_id', $this->filters['owner_id']);
        }

        return $query;
    }

    public function headings(): array
    {
        return ['ID', '姓名', '公司', '电话', '邮箱', '状态', '来源', '负责人', '创建时间'];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->name,
            $lead->company_name,
            $lead->phone,
            $lead->email,
            $lead->status,
            $lead->source?->name,
            $lead->owner?->name,
            $lead->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
