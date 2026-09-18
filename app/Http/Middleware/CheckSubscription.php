<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Check if company subscription is valid.
     * Expired or unsubscribed tenants will be redirected to subscription page.
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

        // Skip check for super admin
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check company status
        if ($company->status !== \App\Enums\CompanyStatus::Active) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Company account expired or disabled'], 403);
            }
            return redirect()->route('subscription.expired');
        }

        // Check if subscription is valid
        $subscription = $company->subscription;
        if ($subscription && !$subscription->isActive()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subscription expired, please renew'], 403);
            }
            return redirect()->route('subscription.expired');
        }

        return $next($request);
    }
}
