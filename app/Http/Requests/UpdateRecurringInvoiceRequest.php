<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecurringInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'sometimes|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'frequency' => 'sometimes|in:daily,weekly,monthly,yearly',
            'interval' => 'nullable|integer|min:1',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'next_invoice_date' => 'nullable|date',
            'sub_total' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'total' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'note' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,completed',
        ];
    }
}
