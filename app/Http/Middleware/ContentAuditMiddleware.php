<?php

namespace App\Http\Middleware;

use App\Services\ContentSecurity\ContentSecurityManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 内容审核中间件 - 在请求处理前对文本内容进行审核
 * 
 * 使用方式：在路由上 ->middleware('content.audit')
 * 中间件会从请求中提取content字段进行审核
 * 如果审核不通过(BLOCK)，则返回422错误
 * 如果需复审(REVIEW)，允许通过但记录标记
 */
class ContentAuditMiddleware
{
    private ContentSecurityManager $auditService;

    public function __construct(ContentSecurityManager $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * 处理请求
     * 
     * @param string|null $contentField 需要审核的内容字段名
     */
    public function handle(Request $request, Closure $next, ?string $contentField = null)
    {
        // 如果审核服务未启用，直接放行
        if (!$this->auditService->isEnabled()) {
            return $next($request);
        }

        // 确定要审核的内容字段
        $fields = $contentField ? explode(',', $contentField) : ['content', 'description', 'body', 'message', 'title', 'name'];
        $contentToAudit = '';

        foreach ($fields as $field) {
            $value = $request->input(trim($field));
            if ($value && is_string($value)) {
                $contentToAudit .= $value . ' ';
            }
        }

        if (empty(trim($contentToAudit))) {
            return $next($request);
        }

        // 执行文本审核
        $result = $this->auditService->auditText(
            trim($contentToAudit),
            null, // auditableType - 在controller中设置
            null, // auditableId - 在controller中设置
        );

        // 将审核结果附加到请求中，供controller使用
        $request->merge(['_audit_result' => $result]);

        // 如果审核拦截，阻止请求
        if (!$result['passed']) {
            Log::warning('Content blocked by audit middleware', [
                'path' => $request->path(),
                'suggestion' => $result['suggestion'] ?? '',
                'labels' => $result['labels'] ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => '内容审核未通过：' . ($result['message'] ?? '内容违规'),
                'errors' => [
                    'content_audit' => [$result['message'] ?? '内容违规，请修改后重新提交'],
                ],
            ], 422);
        }

        return $next($request);
    }
}
