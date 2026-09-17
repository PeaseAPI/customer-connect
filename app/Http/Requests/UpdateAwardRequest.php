<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|exists:users,id',
            'award_icon_id' => 'nullable|exists:award_icons,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'award_date' => 'sometimes|date',
        ];
    }
}
