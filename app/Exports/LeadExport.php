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
    protected int $companyId;

    public function __construct(array $filters = [], int $companyId = 0)
    {
        $this->filters = $filters;
        $this->companyId = $companyId;
    }

    public function query()
    {
        $query = Lead::with(['owner', 'source']);

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        if (!empty($this->filters['status_id'])) {
            $query->where('status_id', $this->filters['status_id']);
        }
        if (!empty($this->filters['source_id'])) {
            $query->where('source_id', $this->filters['source_id']);
        }
        if (!empty($this->filters['agent_id'])) {
            $query->where('agent_id', $this->filters['agent_id']);
        }
        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('lead_name', 'like', "%{$this->filters['search']}%")
                  ->orWhere('lead_email', 'like', "%{$this->filters['search']}%")
                  ->orWhere('lead_mobile', 'like', "%{$this->filters['search']}%");
            });
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
            $lead->lead_name,
            $lead->company_name,
            $lead->lead_mobile,
            $lead->lead_email,
            $lead->status?->stage_name,
            $lead->source?->name,
            $lead->agent?->name,
            $lead->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
