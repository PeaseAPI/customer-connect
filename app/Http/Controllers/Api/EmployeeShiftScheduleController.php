<?php

namespace App\Http\Controllers\Api;

use App\Models\EmployeeShiftSchedule;
use App\Services\HRM\EmployeeShiftScheduleService;
use Illuminate\Http\Request;

class EmployeeShiftScheduleController extends BaseApiController
{
    public function __construct(protected EmployeeShiftScheduleService $shiftScheduleService) {}

    public function index(Request $request, $employeeId)
    {
        $schedules = $this->shiftScheduleService->list($employeeId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($schedules);
    }

            public function store(Request $request, $employeeId)
    {
        $validated = $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'date' => 'required|date',
                        'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'shift_type' => 'nullable|string|in:regular,flexible',
        ]);

        $validated['user_id'] = $employeeId;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->shiftScheduleService->create($validated)->load(['shift']),
            'Shift schedule created',
            201
        );
    }

    public function show($employeeId, EmployeeShiftSchedule $shiftSchedule)
    {
        return $this->success($shiftSchedule->load(['shift']));
    }

        public function update(Request $request, $employeeId, EmployeeShiftSchedule $shiftSchedule)
    {
        $validated = $request->validate([
            'shift_id' => 'sometimes|exists:shifts,id',
            'date' => 'sometimes|date',
            'day_of_week' => 'sometimes|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'end_date' => 'nullable|date|after_or_equal:date',
        ]);

        $shiftSchedule = $this->shiftScheduleService->update($shiftSchedule, $validated);

        return $this->success($shiftSchedule->load(['shift']), 'Updated successfully');
    }

    public function destroy($employeeId, EmployeeShiftSchedule $shiftSchedule)
    {
        $this->shiftScheduleService->delete($shiftSchedule);

        return $this->success(null, 'Deleted successfully');
    }
}
