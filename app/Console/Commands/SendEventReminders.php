<?php
namespace App\Console\Commands;

use App\Models\Event; use App\Events\EventReminderSent;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'cc:event-reminders';
    protected $description = 'Send event reminders';

    public function handle(): int
    {
        $events = Event::where("start_date_time","<=",now()->addHours(1))->where("start_date_time",">",now())->get(); foreach($events as $e){ event(new EventReminderSent($e)); } $this->info("Event reminders: {$events->count()}");
        return self::SUCCESS;
    }
}
