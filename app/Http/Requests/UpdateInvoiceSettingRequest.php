<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_prefix' => 'sometimes|string|max:10',
            'invoice_digits' => 'sometimes|integer|min:1|max:10',
            'estimate_prefix' => 'sometimes|string|max:10',
            'credit_note_prefix' => 'sometimes|string|max:10',
            'invoice_number_separator' => 'nullable|string|max:5',
            'next_invoice_number' => 'sometimes|string',
            'next_estimate_number' => 'sometimes|string',
            'next_credit_note_number' => 'sometimes|string',
            'template' => 'nullable|string|max:50',
            'due_after' => 'nullable|string|max:10',
            'currency_format' => 'nullable|string|max:20',
            'decimal_separator' => 'nullable|string|max:5',
            'thousand_separator' => 'nullable|string|max:5',
            'show_client_note' => 'sometimes|boolean',
            'show_item_tax' => 'sometimes|boolean',
            'invoice_note' => 'nullable|string',
            'estimate_note' => 'nullable|string',
            'credit_note_note' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
        ];
    }
}
