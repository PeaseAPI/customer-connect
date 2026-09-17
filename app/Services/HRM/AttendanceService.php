<?php

namespace App\Services\HRM;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Attendance::with(['user', 'shift']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    public function clockIn(int $userId, array $data = []): ?Attendance
    {
        $existing = Attendance::where('user_id', $userId)
            ->whereDate('date', now()->toDateString())
            ->whereNotNull('clock_in_time')
            ->first();

        if ($existing) {
            return null;
        }

        return Attendance::create([
            'user_id' => $userId,
            'company_id' => $data['company_id'] ?? null,
            'date' => now()->toDateString(),
            'clock_in_time' => now(),
            'clock_in_ip' => $data['ip'] ?? null,
        ]);
    }

    public function clockOut(int $userId, array $data = []): ?Attendance
    {
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', now()->toDateString())
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->first();

        if (!$attendance) {
            return null;
        }

        $attendance->update([
            'clock_out_time' => now(),
            'clock_out_ip' => $data['ip'] ?? null,
            'working_from' => $data['working_from'] ?? null,
        ]);

        return $attendance->fresh();
    }

    public function checkIn(Employee $employee, array $data = []): Attendance
    {
        $today = now()->toDateString();
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->check_in_time) {
            throw new \Exception('今日已签到');
        }

        $checkInTime = now();
        $isLate = $this->isLate($employee, $checkInTime);

        return Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            [
                'company_id' => $employee->company_id,
                'check_in_time' => $checkInTime,
                'check_in_ip' => $data['ip'] ?? request()->ip(),
                'check_in_location' => $data['location'] ?? null,
                'status' => $isLate ? 'late' : 'normal',
                'is_late' => $isLate,
            ]
        );
    }

    public function checkOut(Employee $employee, array $data = []): Attendance
    {
        $today = now()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->firstOrFail();

        $checkOutTime = now();
        $isEarlyLeave = $this->isEarlyLeave($employee, $checkOutTime);

        $workedMinutes = $checkOutTime->diffInMinutes($attendance->check_in_time);
        $overtimeMinutes = max(0, $workedMinutes - $this->getStandardWorkMinutes($employee));

        $attendance->update([
            'check_out_time' => $checkOutTime,
            'check_out_ip' => $data['ip'] ?? request()->ip(),
            'check_out_location' => $data['location'] ?? null,
            'worked_minutes' => $workedMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'is_early_leave' => $isEarlyLeave,
            'status' => $isEarlyLeave ? 'early_leave' : $attendance->status,
        ]);

        return $attendance->fresh();
    }

    public function getMonthlyReport(int $employeeId, string $month): array
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return [
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'work_days' => $this->getWorkDays($startDate, $endDate),
            'attended_days' => $attendances->whereNotNull('check_in_time')->count(),
            'late_count' => $attendances->where('is_late', true)->count(),
            'early_leave_count' => $attendances->where('is_early_leave', true)->count(),
            'absent_count' => $this->getAbsentCount($employeeId, $startDate, $endDate),
            'total_overtime_minutes' => $attendances->sum('overtime_minutes'),
            'total_worked_minutes' => $attendances->sum('worked_minutes'),
        ];
    }

    private function isLate(Employee $employee, Carbon $time): bool
    {
        $workStartTime = $employee->work_start_time ?? '09:00:00';
        return $time->format('H:i:s') > $workStartTime;
    }

    private function isEarlyLeave(Employee $employee, Carbon $time): bool
    {
        $workEndTime = $employee->work_end_time ?? '18:00:00';
        return $time->format('H:i:s') < $workEndTime;
    }

    private function getStandardWorkMinutes(Employee $employee): int
    {
        return $employee->standard_work_minutes ?? 480; // 默认8小时
    }

    private function getWorkDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $current = $start->copy();
        while ($current <= $end) {
            if ($current->isWeekday()) $days++;
            $current->addDay();
        }
        return $days;
    }

    private function getAbsentCount(int $employeeId, Carbon $start, Carbon $end): int
    {
        $workDays = $this->getWorkDays($start, $end);
        $attendedWorkDays = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('check_in_time')
            ->count();
        return max(0, $workDays - $attendedWorkDays);
    }
}
