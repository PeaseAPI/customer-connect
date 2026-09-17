<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:private,group',
            'name' => 'nullable|string|max:255',
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:users,id',
        ];
    }
}
