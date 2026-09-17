<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftChangeRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:shifts,id',
            'current_shift_id' => 'nullable|exists:shifts,id',
            'effective_date' => 'required|date',
            'reason' => 'nullable|string',
        ];
    }
}
