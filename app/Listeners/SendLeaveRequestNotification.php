<?php

namespace App\Listeners;

use App\Events\LeaveRequested;
use App\Notifications\LeaveRequestNotification;
use App\Models\User;

class SendLeaveRequestNotification
{
    public function handle(LeaveRequested $event): void
    {
        $leave = $event->leave;
        $employee = $leave->employee;
        if ($employee && $employee->report_to) {
            $approver = User::find($employee->report_to);
            if ($approver) {
                $approver->notify(new LeaveRequestNotification($leave));
            }
        }
    }
}
