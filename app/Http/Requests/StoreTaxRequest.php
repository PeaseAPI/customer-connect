<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tax_name' => 'required|string|max:255',
            'tax_percent' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'include_in_total' => 'nullable|boolean',
        ];
    }
}
