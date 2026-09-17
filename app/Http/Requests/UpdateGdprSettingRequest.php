<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGdprSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gdpr_enable' => 'sometimes|boolean',
            'privacy_policy' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'show_consent_on_signup' => 'sometimes|boolean',
            'allow_right_to_be_forgotten' => 'sometimes|boolean',
            'allow_data_export' => 'sometimes|boolean',
        ];
    }
}
