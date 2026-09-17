<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRecurringRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_id' => 'required|exists:expenses,id',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'interval' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'next_expense_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,completed',
        ];
    }
}
