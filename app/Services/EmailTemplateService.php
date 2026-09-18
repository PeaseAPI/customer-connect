<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Str;

class EmailTemplateService
{
    public function list(int $companyId, array $filters = [])
    {
        $query = EmailTemplate::forCompany($companyId);

        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('subject', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 20);
    }

    public function create(int $companyId, array $data): EmailTemplate
    {
        $data['company_id'] = $companyId;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return EmailTemplate::create($data);
    }

    public function update(EmailTemplate $template, array $data): EmailTemplate
    {
        if ($template->is_system && isset($data['slug'])) {
            unset($data['slug']);
        }

        $template->update($data);
        return $template->fresh();
    }

    public function delete(EmailTemplate $template): bool
    {
        if ($template->is_system) {
            throw new \Exception('System templates cannot be deleted');
        }

        return $template->delete();
    }

    public function render(EmailTemplate $template, array $variables = []): array
    {
        $subject = $template->subject;
        $body = $template->body;

        foreach ($variables as $key => $value) {
            $subject = str_replace("{{{$key}}}", $value, $subject);
            $body = str_replace("{{{$key}}}", $value, $body);
        }

        return compact('subject', 'body');
    }
}
