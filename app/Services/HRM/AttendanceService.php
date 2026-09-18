<?php

namespace App\Services\HRM;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Attendance::with(['user', 'shift']);

        if (!empty($filters['search'])) {
            $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$filters['search']}%"));
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }
        if (!empty($filters['late'])) {
            $query->where('late', $filters['late']);
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

            public function checkIn(User $user, array $data = []): Attendance
    {
        $today = now()->toDateString();
        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->clock_in_time) {
            throw new \Exception('Already checked in today');
        }

        $clockInTime = now();
        $isLate = $this->isLate($user, $clockInTime);

        return Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'company_id' => $user->company_id,
                'clock_in_time' => $clockInTime,
                'clock_in_ip' => $data['ip'] ?? request()->ip(),
                'late' => $isLate ? 'yes' : 'no',
                'late_by' => $isLate ? $this->getLateMinutes($user, $clockInTime) : 0,
                'shift_id' => $data['shift_id'] ?? null,
            ]
        );
    }

    public function checkOut(User $user, array $data = []): Attendance
    {
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->firstOrFail();

        $clockOutTime = now();

        $attendance->update([
            'clock_out_time' => $clockOutTime,
            'clock_out_ip' => $data['ip'] ?? request()->ip(),
            'working_from' => $data['working_from'] ?? 'office',
        ]);

        return $attendance->fresh();
    }

    public function getMonthlyReport(int $userId, string $month): array
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        return [
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'work_days' => $this->getWorkDays($startDate, $endDate),
            'attended_days' => $attendances->whereNotNull('clock_in_time')->count(),
            'late_count' => $attendances->where('late', 'yes')->count(),
            'half_day_count' => $attendances->where('half_day', 'yes')->count(),
            'absent_count' => $this->getAbsentCount($userId, $startDate, $endDate),
        ];
    }

            private function isLate(User $user, Carbon $time): bool
    {
        $shift = $user->employeeDetail?->shift;
        $workStartTime = $shift?->start_time ?? '09:00:00';
        return $time->format('H:i:s') > $workStartTime;
    }

    private function getLateMinutes(User $user, Carbon $time): int
    {
        $shift = $user->employeeDetail?->shift;
        $workStartTime = Carbon::parse($time->format('Y-m-d') . ' ' . ($shift?->start_time ?? '09:00:00'));
        return max(0, $time->diffInMinutes($workStartTime));
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

        private function getAbsentCount(int $userId, Carbon $start, Carbon $end): int
    {
        $workDays = $this->getWorkDays($start, $end);
        $attendedWorkDays = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('clock_in_time')
            ->count();
        return max(0, $workDays - $attendedWorkDays);
    }
}
