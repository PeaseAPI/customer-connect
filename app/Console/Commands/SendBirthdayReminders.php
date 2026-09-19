<?php
namespace App\Console\Commands;

use App\Models\User; use App\Events\BirthdayReminder;
use Illuminate\Console\Command;

class SendBirthdayReminders extends Command
{
    protected $signature = 'cc:birthday-reminders';
    protected $description = 'Send birthday reminders to company admins';

    public function handle(): int
    {
        $users = User::query()
            ->join('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->whereRaw("DATE_FORMAT(employee_details.date_of_birth, '%m-%d') = ?", [now()->format('m-d')])
            ->whereNull('users.deleted_at')
            ->select('users.*')
            ->get();
        foreach($users as $u){ event(new BirthdayReminder($u)); } $this->info("Birthday reminders: {$users->count()}");
        return self::SUCCESS;
    }
}
