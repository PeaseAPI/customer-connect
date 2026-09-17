<?php

namespace App\Listeners;

use App\Events\LeaveStatusChanged;
use App\Notifications\LeaveStatusNotification;
use App\Models\User;

class SendLeaveStatusNotification
{
    public function handle(LeaveStatusChanged $event): void
    {
        $leave = $event->leave;
        $employee = $leave->employee;
        if ($employee && $employee->user) {
            $employee->user->notify(new LeaveStatusNotification($leave, $event->newStatus));
        }
    }
}
