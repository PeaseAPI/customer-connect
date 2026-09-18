<?php

namespace App\Services\Company;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Context;

class ActivityLogService
{
    /**
     * 获取Activity log列表
     */
    public function list(int $companyId, array $filters = [], int $perPage = 15)
    {
        $query = ActivityLog::where('company_id', $companyId)
            ->with('user');

        if (!empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * 获取某实体的Activity log
     */
    public function getForSubject(string $subjectType, int $subjectId, int $perPage = 15)
    {
        return ActivityLog::where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * 清理旧日志
     */
    public function cleanup(int $companyId, int $daysToKeep = 90): int
    {
        return ActivityLog::where('company_id', $companyId)
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }
}
