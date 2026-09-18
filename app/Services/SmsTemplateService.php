<?php

namespace App\Services;

use App\Models\SmsTemplate;
use Illuminate\Support\Str;

class SmsTemplateService
{
    public function list(int $companyId, array $filters = [])
    {
        $query = SmsTemplate::forCompany($companyId);

        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 20);
    }

    public function create(int $companyId, array $data): SmsTemplate
    {
        $data['company_id'] = $companyId;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return SmsTemplate::create($data);
    }

    public function update(SmsTemplate $template, array $data): SmsTemplate
    {
        if ($template->is_system && isset($data['slug'])) {
            unset($data['slug']);
        }

        $template->update($data);
        return $template->fresh();
    }

    public function delete(SmsTemplate $template): bool
    {
        if ($template->is_system) {
            throw new \Exception('System templates cannot be deleted');
        }

        return $template->delete();
    }

    public function render(SmsTemplate $template, array $variables = []): string
    {
        $body = $template->body;

        foreach ($variables as $key => $value) {
            $body = str_replace("{{{$key}}}", $value, $body);
        }

        return $body;
    }
}
