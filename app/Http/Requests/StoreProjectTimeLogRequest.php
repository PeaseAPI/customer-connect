<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectTimeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_id' => 'nullable|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'log_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'total_minutes' => 'nullable|integer',
            'total_hours' => 'nullable|numeric',
            'note' => 'nullable|string',
            'billable' => 'nullable|boolean',
        ];
    }
}
