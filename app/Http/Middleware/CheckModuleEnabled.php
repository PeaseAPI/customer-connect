<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    /**
     * Check if the specified module is enabled in the current plan.
     * Usage in routes: ->middleware('module:hrm') or ->middleware('module:crm,pm')
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
                        'message' => "Module [{$module}] not available, please upgrade your plan",
                    ], 403);
                }
                abort(403, "Module [{$module}] not available, please upgrade your plan");
            }
        }

        return $next($request);
    }
}
