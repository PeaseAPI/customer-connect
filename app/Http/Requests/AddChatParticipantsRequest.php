<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddChatParticipantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:users,id',
        ];
    }
}
