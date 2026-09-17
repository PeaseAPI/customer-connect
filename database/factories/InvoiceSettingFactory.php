<?php

namespace Database\Factories;

use App\Models\InvoiceSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceSetting>
 */
class InvoiceSettingFactory extends Factory
{
    protected $model = InvoiceSetting::class;

    public function definition(): array
    {
        return [
            'company_id' => \App\Models\Company::factory(),
            'invoice_prefix' => 'INV',
            'invoice_digits' => 3,
            'estimate_prefix' => 'EST',
            'credit_note_prefix' => 'CN',
            'invoice_number_separator' => '-',
            'next_invoice_number' => '1',
            'next_estimate_number' => '1',
            'next_credit_note_number' => '1',
            'template' => 'default',
            'due_after' => '30',
            'decimal_separator' => '.',
            'thousand_separator' => ',',
            'show_client_note' => true,
            'show_item_tax' => false,
        ];
    }
}
