<?php

namespace App\Services;

use App\Models\EmailLog;

class EmailLogService
{
    public function list(int $companyId, array $filters = [])
    {
        $query = EmailLog::forCompany($companyId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['emailable_type'])) {
            $query->where('emailable_type', $filters['emailable_type']);
        }

        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('subject', 'like', "%{$filters['search']}%")
                  ->orWhere('from', 'like', "%{$filters['search']}%");
            });
        }

        return $query->with('emailable')->orderByDesc('created_at')->paginate($filters['per_page'] ?? 20);
    }

    public function show(EmailLog $log): EmailLog
    {
        return $log->load('emailable');
    }

    public function delete(EmailLog $log): bool
    {
        return $log->delete();
    }

    public function cleanup(int $companyId, int $daysOld = 30): int
    {
        return EmailLog::forCompany($companyId)
            ->where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }

    public function resend(EmailLog $log): EmailLog
    {
        // Create a new log entry with same data but pending status
        $newLog = EmailLog::create([
            'company_id' => $log->company_id,
            'from' => $log->from,
            'to' => $log->to,
            'cc' => $log->cc,
            'bcc' => $log->bcc,
            'subject' => $log->subject,
            'body' => $log->body,
            'status' => 'pending',
            'emailable_type' => $log->emailable_type,
            'emailable_id' => $log->emailable_id,
        ]);

        return $newLog;
    }
}
