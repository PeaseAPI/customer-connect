<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'award_icon_id' => 'nullable|exists:award_icons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'award_date' => 'required|date',
        ];
    }
}
