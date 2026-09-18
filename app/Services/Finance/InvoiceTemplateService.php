<?php

namespace App\Services\Finance;

use App\Models\InvoiceTemplate;
use Illuminate\Support\Str;

class InvoiceTemplateService
{
    /**
     * List invoice templates
     */
    public function list(int $companyId, int $perPage = 15)
    {
        return InvoiceTemplate::where('company_id', $companyId)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Create invoice template
     */
    public function create(int $companyId, array $data): InvoiceTemplate
    {
        $data['company_id'] = $companyId;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        if (!empty($data['is_default'])) {
            // 取消Other默认模板
            InvoiceTemplate::where('company_id', $companyId)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        return InvoiceTemplate::create($data);
    }

    /**
     * Update invoice template
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
     * Delete invoice template
     */
    public function delete(InvoiceTemplate $template): void
    {
        if ($template->is_default) {
            // 如果删除默认模板，将No.一 可用模板设为默认
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
     * 从现有Invoice created模板
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
