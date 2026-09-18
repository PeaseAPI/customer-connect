<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldQueue;

class ClientExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
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
        $query = Client::with(['owner', 'level', 'source', 'tags']);

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        if (!empty($this->filters['level_id'])) {
            $query->where('level_id', $this->filters['level_id']);
        }
        if (!empty($this->filters['owner_id'])) {
            $query->where('owner_id', $this->filters['owner_id']);
        }
        if (!empty($this->filters['industry'])) {
            $query->where('industry', $this->filters['industry']);
        }
        if (!empty($this->filters['search'])) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->filters['search']}%")
                  ->orWhere('contact_name', 'like', "%{$this->filters['search']}%")
                  ->orWhere('contact_email', 'like', "%{$this->filters['search']}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID', '客户名称', '行业', '等级', '来源',
            '联系人', '联系电话', '邮箱', '地址',
            '负责人', '标签', '创建时间',
        ];
    }

    public function map($client): array
    {
        return [
            $client->id,
            $client->name,
            $client->industry,
            $client->level?->category_name,
            $client->source?->name,
            $client->contact_name,
            $client->contact_phone,
            $client->contact_email,
            $client->address,
            $client->owner?->name,
            $client->tags->pluck('name')->implode(', '),
            $client->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
