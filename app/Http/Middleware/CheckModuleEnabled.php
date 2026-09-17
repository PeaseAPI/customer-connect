<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    /**
     * 检查指定模块是否在当前套餐中启用。
     * 在路由中使用: ->middleware('module:hrm') 或 ->middleware('module:crm,pm')
     */
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $user = $request->user();

        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $company = $user->company;

        if (!$company) {
            return $next($request);
        }

        $subscription = $company->subscription;

        if (!$subscription) {
            return $next($request);
        }

        $package = $subscription->package;

        if (!$package) {
            return $next($request);
        }

        $enabledModules = $package->modules ?? [];

        foreach ($modules as $module) {
            if (!in_array($module, $enabledModules)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => "模块 [{$module}] 未开通，请升级套餐",
                    ], 403);
                }
                abort(403, "模块 [{$module}] 未开通，请升级套餐");
            }
        }

        return $next($request);
    }
}
