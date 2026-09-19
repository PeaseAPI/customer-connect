<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeReminderNotification;
use Illuminate\Console\Command;

class SendAttendanceReminders extends Command
{
    protected $signature = 'cc:attendance-reminders';
    protected $description = 'Send clock-in reminders to employees';

    public function handle(): int
    {
        $today = today()->toDateString();
        $clockedUserIds = \DB::table('attendances')->whereDate('date', $today)->pluck('user_id')->all();

        $employees = Employee::where('status', 'active')->whereNotNull('user_id')->get();
        $reminded = 0;
        foreach ($employees as $employee) {
            if (in_array($employee->user_id, $clockedUserIds)) {
                continue;
            }

            $user = User::find($employee->user_id);
            if (! $user) {
                continue;
            }

            $user->notify(new EmployeeReminderNotification('考勤打卡提醒', [
                'date' => $today,
                'employee_code' => $employee->employee_code,
            ]));
            $reminded++;
        }

        $this->info("Attendance reminders sent: {$reminded}");
        return self::SUCCESS;
    }
}
