<?php
namespace App\Console\Commands;

use App\Models\Project;
use App\Events\ProjectReminder;
use Illuminate\Console\Command;

class SendProjectReminders extends Command
{
    protected $signature = 'cc:project-reminders';
    protected $description = 'Send reminders for projects approaching deadline';

    public function handle(): int
    {
        $projects = Project::whereNotNull('end_date')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->where('end_date', '<=', now()->addDays(3))
            ->where('end_date', '>=', now())
            ->get();

        foreach ($projects as $project) {
            event(new ProjectReminder($project));
        }

        $this->info("Project reminders sent: {$projects->count()}");
        return self::SUCCESS;
    }
}
