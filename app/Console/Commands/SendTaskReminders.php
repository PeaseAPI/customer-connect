<?php
namespace App\Console\Commands;

use App\Models\Task;
use App\Events\TaskReminder;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature = 'cc:task-reminders';
    protected $description = 'Send reminders for tasks approaching deadline';

    public function handle(): int
    {
        $tasks = Task::whereNotNull('end_date')
            ->where('status', '!=', 'completed')
            ->where('end_date', '<=', now()->addDays(1))
            ->where('end_date', '>=', now())
            ->get();

        foreach ($tasks as $task) {
            event(new TaskReminder($task));
        }

        $this->info("Task reminders sent: {$tasks->count()}");
        return self::SUCCESS;
    }
}
