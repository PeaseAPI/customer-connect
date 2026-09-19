<?php

namespace App\Services;

use Illuminate\Support\Facades\Context;

/**
 * 公司上下文服务 - 统一获取当前请求的公司ID
 *
 * 当前公司ID由中间件从认证用户/请求头解析后写入 Laravel Context,
 * 供 Controller/Listener/Model(HasCompanyScope) 一致地取用。
 */
class ContextService
{
    public function getCompanyId(): ?int
    {
        $value = Context::get('current_company_id');

        return $value === null ? null : (int) $value;
    }
}
