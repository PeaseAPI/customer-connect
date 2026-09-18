<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * 确保当前用户是超级管理员。
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '无权访问，仅超级管理员可操作'], 403);
            }
            abort(403, '无权访问，仅超级管理员可操作');
        }

        return $next($request);
    }
}
