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
