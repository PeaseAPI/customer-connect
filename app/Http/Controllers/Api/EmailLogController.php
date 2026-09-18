<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\EmailLog;
use App\Services\EmailLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailLogController extends BaseApiController
{
    public function __construct(protected EmailLogService $emailLogService) {}

    public function index(Request $request): JsonResponse
    {
        $companyId = $request->attributes->get('company_id');
        $logs = $this->emailLogService->list($companyId, $request->all());
        return $this->paginated($logs);
    }

    public function show(EmailLog $emailLog): JsonResponse
    {
        $log = $this->emailLogService->show($emailLog);
        return $this->success($log);
    }

    public function destroy(EmailLog $emailLog): JsonResponse
    {
        $this->emailLogService->delete($emailLog);
        return $this->success(null, 'Email log deleted');
    }

    public function resend(EmailLog $emailLog): JsonResponse
    {
        $newLog = $this->emailLogService->resend($emailLog);
        return $this->success($newLog, 'Email queued for resend');
    }

    public function cleanup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days_old' => 'integer|min:1|max:365',
        ]);

        $companyId = $request->attributes->get('company_id');
        $deleted = $this->emailLogService->cleanup($companyId, $validated['days_old'] ?? 30);
        return $this->success(['deleted_count' => $deleted], 'Email logs cleaned up');
    }
}
