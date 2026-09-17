<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unit_type' => 'sometimes|string|max:255|unique:unit_types,unit_type,' . request()->route('unitType'),
        ];
    }
}
