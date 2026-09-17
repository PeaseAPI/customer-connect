<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRecurringRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'frequency' => 'sometimes|in:daily,weekly,monthly,yearly',
            'interval' => 'nullable|integer|min:1',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'next_expense_date' => 'nullable|date',
            'status' => 'sometimes|in:active,inactive,completed',
        ];
    }
}
