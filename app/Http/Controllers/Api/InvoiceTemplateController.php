<?php

namespace App\Http\Controllers\Api;

use App\Models\InvoiceTemplate;
use App\Services\Finance\InvoiceTemplateService;
use Illuminate\Http\Request;

class InvoiceTemplateController extends BaseApiController
{
    public function __construct(protected InvoiceTemplateService $templateService) {}

    /**
     * 列出发票模板
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $templates = $this->templateService->list($companyId, $request->per_page ?? 15);
        return $this->paginated($templates);
    }

    /**
     * 创建发票模板
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:100|alpha_dash',
            'header_html' => 'nullable|string',
            'body_html' => 'nullable|string',
            'footer_html' => 'nullable|string',
            'css' => 'nullable|string',
            'settings' => 'nullable|array',
            'settings.show_logo' => 'nullable|boolean',
            'settings.show_company_info' => 'nullable|boolean',
            'settings.show_payment_details' => 'nullable|boolean',
            'settings.color_primary' => 'nullable|string|max:7',
            'is_default' => 'nullable|boolean',
        ]);

        $companyId = $request->attributes->get('company_id');
        $template = $this->templateService->create($companyId, $validated);

        return $this->success($template, '发票模板创建成功', 201);
    }

    /**
     * 查看发票模板详情
     */
    public function show(InvoiceTemplate $invoiceTemplate)
    {
        return $this->success($invoiceTemplate);
    }

    /**
     * 更新发票模板
     */
    public function update(Request $request, InvoiceTemplate $invoiceTemplate)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'header_html' => 'nullable|string',
            'body_html' => 'nullable|string',
            'footer_html' => 'nullable|string',
            'css' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $template = $this->templateService->update($invoiceTemplate, $validated);
        return $this->success($template, '更新成功');
    }

    /**
     * 删除发票模板
     */
    public function destroy(InvoiceTemplate $invoiceTemplate)
    {
        $this->templateService->delete($invoiceTemplate);
        return $this->success(null, '发票模板已删除');
    }

    /**
     * 设为默认模板
     */
    public function setDefault(InvoiceTemplate $invoiceTemplate)
    {
        $template = $this->templateService->setDefault($invoiceTemplate);
        return $this->success($template, '已设为默认模板');
    }

    /**
     * 预览模板（用示例数据渲染）
     */
    public function preview(InvoiceTemplate $invoiceTemplate)
    {
        $settings = array_merge([
            'show_logo' => true,
            'show_company_info' => true,
            'show_payment_details' => true,
            'color_primary' => '#4f46e5',
        ], $invoiceTemplate->settings ?? []);

        $css = $invoiceTemplate->css ?? $invoiceTemplate->getDefaultCss();

        return $this->success([
            'header_html' => $invoiceTemplate->header_html,
            'body_html' => $invoiceTemplate->body_html,
            'footer_html' => $invoiceTemplate->footer_html,
            'css' => $css,
            'settings' => $settings,
        ]);
    }
}
