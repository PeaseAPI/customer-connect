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
        $users = User::whereRaw("DATE_FORMAT(birth_date, '%m-%d') = ?", [now()->format('m-d')])->get(); foreach($users as $u){ event(new BirthdayReminder($u)); } $this->info("Birthday reminders: {$users->count()}");
        return self::SUCCESS;
    }
}
