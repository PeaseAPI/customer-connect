<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * 检查公司订阅是否有效。
     * 过期或未订阅的租户将被重定向到订阅页面。
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $company = $user->company;

        if (!$company) {
            return $next($request);
        }

        // 超级管理员跳过检查
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // 检查公司状态
        if ($company->status !== \App\Enums\CompanyStatus::Active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '公司账户已过期或被禁用'], 403);
            }
            return redirect()->route('subscription.expired');
        }

        // 检查订阅是否有效
        $subscription = $company->subscription;
        if ($subscription && !$subscription->isActive()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '订阅已过期，请续费'], 403);
            }
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
