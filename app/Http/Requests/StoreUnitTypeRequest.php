<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_type' => 'required|string|max:255|unique:unit_types,unit_type,NULL,id,company_id,' . (request()->user()?->company_id ?? request()->input('company_id')),
        ];
    }
}
