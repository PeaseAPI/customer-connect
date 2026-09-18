<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Services\Company\ApiKeyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function __construct(protected ApiKeyService $apiKeyService) {}

    /**
     * 通过 API Key 进行身份验证。
     * 支持两种方式:
     * 1. Header: X-API-Key: kht_xxxxx
     * 2. Query: ?api_key=kht_xxxxx
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $plainKey = $request->header('X-API-Key')
            ?? $request->query('api_key');

        if (!$plainKey) {
            return response()->json(['message' => '缺少 API Key'], 401);
        }

        $apiKey = $this->apiKeyService->validate($plainKey);

        if (!$apiKey) {
            return response()->json(['message' => '无效或已过期的 API Key'], 401);
        }

        // 检查权限
        foreach ($permissions as $permission) {
            if (!$apiKey->hasPermission($permission)) {
                return response()->json([
                    'message' => "API Key 无权访问模块 [{$permission}]",
                ], 403);
            }
        }

        // 设置公司上下文
        $request->attributes->set('company_id', $apiKey->company_id);
        $request->attributes->set('api_key_id', $apiKey->id);

        // 设置用户上下文（用于审计日志等）
        $request->setUserResolver(fn () => $apiKey->user);

        return $next($request);
    }
}
