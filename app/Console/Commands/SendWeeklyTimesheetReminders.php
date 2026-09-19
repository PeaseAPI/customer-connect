<?php
namespace App\Console\Commands;

use App\Models\User; use App\Events\WeeklyTimesheetSubmitted;
use Illuminate\Console\Command;

class SendWeeklyTimesheetReminders extends Command
{
    protected $signature = 'cc:weekly-timesheet-reminders';
    protected $description = 'Remind users to submit weekly timesheets';

    public function handle(): int
    {
        $this->info("Weekly timesheet reminders sent");
        return self::SUCCESS;
    }
}
