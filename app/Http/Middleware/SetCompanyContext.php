<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class SetCompanyContext
{
    /**
     * 从子域名或认证用户解析公司ID并设置到Context中。
     * 所有使用HasCompanyScope的模型将自动通过CompanyScope过滤。
     */
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = null;

        // 1. 优先从子域名解析
        $host = $request->getHost();
        $parts = explode('.', $host);

        // 如果是子域名访问 (例如: company.kht.cn)
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            $company = \App\Models\Company::where('subdomain', $subdomain)->first();
            if ($company) {
                $companyId = $company->id;
            }
        }

        // 2. 从认证用户解析（API请求或子域名未匹配时）
        if (!$companyId && $request->user()) {
            $companyId = $request->user()->company_id;
        }

        // 3. 从请求头解析（适用于API调用时通过Header传递）
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

