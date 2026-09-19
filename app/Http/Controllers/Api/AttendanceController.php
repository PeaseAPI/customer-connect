<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Services\HRM\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends BaseApiController
{
    public function __construct(protected AttendanceService $attendanceService) {}

    public function index(Request $request)
    {
        $attendances = $this->attendanceService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($attendances);
    }

    public function show(Attendance $attendance)
    {
        return $this->success($attendance->load(['user', 'shift']));
    }

    /**
     * 手工补录考勤记录 (管理员)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'clock_in_time' => 'required|date',
            'clock_out_time' => 'nullable|date|after:clock_in_time',
            'shift_id' => 'nullable|exists:shifts,id',
            'working_from' => 'nullable|in:office,home,other',
        ]);
        $validated['company_id'] = $request->attributes->get('company_id');

        $exists = Attendance::where('user_id', $validated['user_id'])
            ->where('date', $validated['date'])
            ->exists();
        if ($exists) {
            return $this->error('Attendance already exists for this user and date', 422);
        }

        $attendance = Attendance::create($validated);

        return $this->success($attendance->load(['user', 'shift']), 'Attendance created successfully', 201);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'clock_in_time' => 'sometimes|date',
            'clock_out_time' => 'nullable|date|after:clock_in_time',
            'shift_id' => 'nullable|exists:shifts,id',
            'working_from' => 'nullable|in:office,home,other',
            'late' => 'nullable|boolean',
            'half_day' => 'nullable|boolean',
        ]);

        $attendance->update($validated);

        return $this->success($attendance->fresh()->load(['user', 'shift']), 'Attendance updated successfully');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return $this->success(null, 'Attendance deleted');
    }

    public function clockIn(Request $request)
    {
        $data = $this->attendanceService->clockIn(Auth::id(), [
            'company_id' => $request->attributes->get('company_id'),
            'ip' => $request->ip(),
        ]);

        if (!$data) {
            return $this->error('Already clocked in today', 422);
        }

        return $this->success($data->load(['user', 'shift']), 'Clocked in successfully', 201);
    }

    public function clockOut(Request $request)
    {
        $data = $this->attendanceService->clockOut(Auth::id(), [
            'ip' => $request->ip(),
            'working_from' => $request->working_from ?? 'office',
        ]);

        if (!$data) {
            return $this->error('No clock-in record found or already clocked out', 404);
        }

        return $this->success($data->load(['user', 'shift']), 'Clocked out successfully');
    }
}
