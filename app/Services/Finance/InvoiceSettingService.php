<?php

namespace App\Services\Finance;

use App\Models\InvoiceSetting;

class InvoiceSettingService
{
    public function get(int $companyId): InvoiceSetting
    {
        return InvoiceSetting::firstOrCreate(
            ['company_id' => $companyId],
            ['invoice_prefix' => 'INV', 'estimate_prefix' => 'EST', 'credit_note_prefix' => 'CN']
        );
    }

    public function update(int $companyId, array $data): InvoiceSetting
    {
        return InvoiceSetting::updateOrCreate(
            ['company_id' => $companyId],
            $data
        );
    }
}

