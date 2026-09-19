<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ContentAuditLog;
use App\Services\ContentSecurity\ContentSecurityManager;
use Illuminate\Http\Request;

class ContentAuditLogController extends BaseApiController
{
    private ContentSecurityManager $auditService;

    public function __construct(ContentSecurityManager $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * 审核记录列表
     */
    public function index(Request $request)
    {
        $request->validate([
            'content_type' => 'nullable|string|in:text,image,video,audio',
            'suggestion' => 'nullable|string|in:PASS,REVIEW,BLOCK,PENDING',
            'risk_level' => 'nullable|string|in:NONE,LOW,MEDIUM,HIGH',
            'status' => 'nullable|string|in:PROCESSING,COMPLETED,FAILED',
            'auditable_type' => 'nullable|string',
            'auditable_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = ContentAuditLog::query()
            ->with(['user:id,name', 'auditable']);

        foreach (['content_type', 'suggestion', 'risk_level', 'status', 'auditable_type'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
        if ($request->filled('auditable_id')) {
            $query->where('auditable_id', $request->input('auditable_id'));
        }

        $records = $query->orderByDesc('id')
            ->paginate($request->input('per_page', 20));

        return $this->paginated($records);
    }

    /**
     * 审核记录详情
     */
    public function show(int $id)
    {
        $record = ContentAuditLog::with(['user', 'auditable'])->findOrFail($id);
        return $this->success($record);
    }

    /**
     * 被拦截的记录
     */
    public function blocked(Request $request)
    {
        $records = ContentAuditLog::blocked()
            ->with(['user:id,name', 'auditable'])
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 20));

        return $this->paginated($records);
    }

    /**
     * 需要复审的记录
     */
    public function reviewPending(Request $request)
    {
        $records = ContentAuditLog::needsReview()
            ->with(['user:id,name', 'auditable'])
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 20));

        return $this->paginated($records);
    }

    /**
     * 手动复审 - 更新审核建议
     */
    public function review(Request $request, int $id)
    {
        $request->validate([
            'suggestion' => 'required|string|in:PASS,BLOCK',
            'note' => 'nullable|string|max:500',
        ]);

        $record = ContentAuditLog::findOrFail($id);
        $record->update([
            'suggestion' => $request->input('suggestion'),
            'status' => 'COMPLETED',
            'audited_at' => now(),
        ]);

        return $this->success($record, '复审完成');
    }

    /**
     * 审核统计
     */
    public function statistics()
    {
        $companyId = app('App\Services\ContextService')->getCompanyId();

        $stats = ContentAuditLog::where('company_id', $companyId)
            ->selectRaw("
                content_type,
                suggestion,
                COUNT(*) as count
            ")
            ->groupBy('content_type', 'suggestion')
            ->get();

        return $this->success($stats);
    }

    /**
     * 服务状态
     */
    public function status()
    {
        return $this->success([
            'enabled' => $this->auditService->isEnabled(),
            'configured' => app(\App\Services\Ecloud\EcloudClient::class)->isConfigured(),
        ]);
    }
}
