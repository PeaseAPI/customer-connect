<?php

namespace App\Services\Finance;

use App\Models\InvoiceTemplate;
use Illuminate\Support\Str;

class InvoiceTemplateService
{
    /**
     * 列出发票模板
     */
    public function list(int $companyId, int $perPage = 15)
    {
        return InvoiceTemplate::where('company_id', $companyId)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * 创建发票模板
     */
    public function create(int $companyId, array $data): InvoiceTemplate
    {
        $data['company_id'] = $companyId;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (!empty($data['is_default'])) {
            // 取消其他默认模板
            InvoiceTemplate::where('company_id', $companyId)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        return InvoiceTemplate::create($data);
    }

    /**
     * 更新发票模板
     */
    public function update(InvoiceTemplate $template, array $data): InvoiceTemplate
    {
        if (!empty($data['is_default']) && !$template->is_default) {
            InvoiceTemplate::where('company_id', $template->company_id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $template->update($data);
        return $template->fresh();
    }

    /**
     * 删除发票模板
     */
    public function delete(InvoiceTemplate $template): void
    {
        if ($template->is_default) {
            // 如果删除默认模板，将第一个可用模板设为默认
            $nextDefault = InvoiceTemplate::where('company_id', $template->company_id)
                ->where('id', '!=', $template->id)
                ->where('is_active', true)
                ->first();

            if ($nextDefault) {
                $nextDefault->update(['is_default' => true]);
            }
        }

        $template->delete();
    }

    /**
     * 设为默认模板
     */
    public function setDefault(InvoiceTemplate $template): InvoiceTemplate
    {
        InvoiceTemplate::where('company_id', $template->company_id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $template->update(['is_default' => true]);
        return $template->fresh();
    }

    /**
     * 从现有发票创建模板
     */
    public function createFromInvoice(int $companyId, string $name, int $invoiceId): InvoiceTemplate
    {
        $invoice = \App\Models\Invoice::findOrFail($invoiceId);

        return $this->create($companyId, [
            'name' => $name,
            'settings' => [
                'show_logo' => true,
                'show_company_info' => true,
                'show_payment_details' => true,
            ],
        ]);
    }
}
