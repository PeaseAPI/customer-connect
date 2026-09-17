<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustLeaveQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:added,deducted,reset',
            'amount' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ];
    }
}
