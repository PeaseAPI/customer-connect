<?php

namespace App\Http\Controllers\Api;

use App\Services\Company\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends BaseApiController
{
    public function __construct(protected ActivityLogService $activityLogService) {}

    /**
     * 获取活动日志列表
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');

        $filters = $request->only([
            'log_name', 'event', 'user_id',
            'subject_type', 'subject_id',
            'date_from', 'date_to', 'search',
        ]);

        $logs = $this->activityLogService->list($companyId, $filters, $request->per_page ?? 15);
        return $this->paginated($logs);
    }

    /**
     * 获取某实体的活动日志
     */
    public function forSubject(Request $request, string $subjectType, int $subjectId)
    {
        $logs = $this->activityLogService->getForSubject(
            $subjectType,
            $subjectId,
            $request->per_page ?? 15
        );
        return $this->paginated($logs);
    }

    /**
     * 清理旧日志
     */
    public function cleanup(Request $request)
    {
        $companyId = $request->attributes->get('company_id');

        $validated = $request->validate([
            'days_to_keep' => 'nullable|integer|min:30|max:365',
        ]);

        $deleted = $this->activityLogService->cleanup(
            $companyId,
            $validated['days_to_keep'] ?? 90
        );

        return $this->success(['deleted' => $deleted], "已清理 {$deleted} 条旧日志");
    }
}
