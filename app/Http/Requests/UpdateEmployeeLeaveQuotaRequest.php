<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeLeaveQuotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_of_leaves' => 'sometimes|integer|min:0',
            'leaves_used' => 'sometimes|integer|min:0',
            'cycle' => 'nullable|string',
        ];
    }
}
