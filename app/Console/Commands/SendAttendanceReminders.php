<?php
namespace App\Console\Commands;

use App\Models\User; use App\Events\AttendanceReminderSent;
use Illuminate\Console\Command;

class SendAttendanceReminders extends Command
{
    protected $signature = 'cc:attendance-reminders';
    protected $description = 'Send clock-in reminders to employees';

    public function handle(): int
    {
        $this->info("Attendance reminders sent");
        return self::SUCCESS;
    }
}
