<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class SetCompanyContext
{
    /**
     * Resolve company ID from subdomain or authenticated user and set it in Context.
     * All models using HasCompanyScope will automatically be filtered through CompanyScope.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = null;

        // 1. Resolve from subdomain first
        $host = $request->getHost();
        $parts = explode('.', $host);

        // Sub-domain access (e.g.: company.example.com)
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            $company = \App\Models\Company::where('subdomain', $subdomain)->first();
            if ($company) {
                $companyId = $company->id;
            }
        }

        // 2. Resolve from authenticated user (API requests or no subdomain match)
        if (!$companyId && $request->user()) {
            $companyId = $request->user()->company_id;
        }

        // 3. Resolve from request header (for API calls with Header)
        if (!$companyId) {
            $companyId = $request->header('X-Company-Id');
        }

        if ($companyId) {
            Context::add('current_company_id', (int) $companyId);
            $request->attributes->set('company_id', (int) $companyId);
        }

        return $next($request);
    }
}

