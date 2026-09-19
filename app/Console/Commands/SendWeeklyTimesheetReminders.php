<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\EmployeeReminderNotification;
use Illuminate\Console\Command;

class SendWeeklyTimesheetReminders extends Command
{
    protected $signature = 'cc:weekly-timesheet-reminders';
    protected $description = 'Remind users to submit weekly timesheets';

    public function handle(): int
    {
        $week = now()->format('o-W');
        $employees = Employee::where('status', 'active')->whereNotNull('user_id')->get();

        $reminded = 0;
        foreach ($employees as $employee) {
            $user = User::find($employee->user_id);
            if (! $user) {
                continue;
            }

            $user->notify(new EmployeeReminderNotification('周报提交提醒', [
                'week' => $week,
                'employee_code' => $employee->employee_code,
            ]));
            $reminded++;
        }

        $this->info("Weekly timesheet reminders sent: {$reminded}");
        return self::SUCCESS;
    }
}
