<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeShiftScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shift_id' => 'sometimes|exists:shifts,id',
            'date' => 'sometimes|date',
            'day_of_week' => 'sometimes|string',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'shift_type' => 'nullable|in:regular,flexible',
        ];
    }
}
