<?php

namespace App\Http\Controllers\Api;

use App\Services\ContentSecurity\ContentSecurityInterface;
use App\Services\ContentSecurity\ContentSecurityManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 内容审核回调控制器 - 多Provider异步审核结果回调统一入口
 *
 * 支持: 移动云(ecloud) | 阿里云(aliyun) | 腾讯云(tencent)
 *
 * 路由:
 * - POST content-audit/callback/{provider} 显式指定Provider(推荐, 最可靠)
 * - POST content-audit/callback            根据回调payload结构自动识别
 * - POST ecloud/audit-callback             兼容旧路由(自动识别)
 *
 * 回调payload特征识别规则:
 * - tencent: 顶层含 "Results" 键 ({Results:[{TaskId,Suggestion,...}]})
 * - aliyun:  含 "dataId"+"taskId" (阿里云回调为数组 [{code,dataId,taskId,results}])
 * - ecloud:  含 "requestId" ({requestId,suggestion,hits})
 */
class ContentAuditCallbackController extends BaseApiController
{
    public function __construct(private ContentSecurityManager $manager)
    {
    }

    /**
     * 接收内容审核异步回调
     */
    public function callback(Request $request, ?string $provider = null)
    {
        $data = $request->json()->all() ?: $request->all();

        Log::info('Content audit callback received', [
            'route_provider' => $provider,
            'ip'             => $request->ip(),
            'data'           => $data,
        ]);

        try {
            if ($provider !== null) {
                // 显式指定Provider - 通过路由参数
                $resolvedProvider = $provider;
                $handler          = $this->manager->driver($provider);
            } else {
                // 自动识别Provider
                [$resolvedProvider, $handler] = $this->detectProvider($data);
            }

            $this->verifyCallbackSign($request, $resolvedProvider);

            $log = $handler->handleCallback($data);

            Log::info('Content audit callback processed', [
                'provider' => $resolvedProvider,
                'task_id'  => $log->task_id,
                'suggestion' => $log->suggestion,
            ]);

            // 各云厂商一般只要求HTTP 200, 统一返回简单结构
            return response()->json([
                'code'     => 0,
                'message'  => 'success',
                'provider' => $resolvedProvider,
            ]);
        } catch (\Throwable $e) {
            Log::error('Content audit callback error', [
                'route_provider' => $provider,
                'message'        => $e->getMessage(),
                'data'           => $data,
            ]);

            return response()->json([
                'code'    => -1,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 根据回调payload结构识别Provider
     *
     * @return array{0: string, 1: ContentSecurityInterface}
     */
    private function detectProvider(array $data): array
    {
        // 腾讯云: {"Results": [{"BizType":..., "TaskId":..., "Suggestion":...}]}
        if (isset($data['Results'])) {
            return ['tencent', $this->manager->driver('tencent')];
        }

        // 阿里云: [{"code":200,"dataId":"...","taskId":"...","results":[...]}] 或对象形式
        $item = $data[0] ?? $data;
        if (isset($item['dataId'], $item['taskId'])) {
            return ['aliyun', $this->manager->driver('aliyun')];
        }

        // 移动云: {"requestId": "...", "suggestion": "...", "hits": [...]}
        if (isset($data['requestId'])) {
            return ['ecloud', $this->manager->driver('ecloud')];
        }

        throw new \RuntimeException(
            '无法识别回调来源Provider, 请使用显式路由 POST /content-audit/callback/{provider}'
        );
    }

    /**
     * 验证回调签名(目前仅移动云支持)
     *
     * 签名方式: Base64(HMAC-SHA256(SecretKey, requestBody))
     * 签名头不存在时跳过验证(根据移动云实际配置决定)
     */
    private function verifyCallbackSign(Request $request, string $provider): void
    {
        if ($provider !== 'ecloud') {
            return;
        }

        $sign = $request->header('x-ecloud-signature');
        if (!$sign) {
            return;
        }

        $secretKey    = config('services.ecloud.secret_key', '');
        $body         = $request->getContent();
        $expectedSign = base64_encode(hash_hmac('sha256', $body, $secretKey, true));

        if (!hash_equals($expectedSign, $sign)) {
            throw new \RuntimeException('回调签名验证失败');
        }
    }
}
