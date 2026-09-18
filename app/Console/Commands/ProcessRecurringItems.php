<?php

namespace App\Console\Commands;

use App\Services\PM\RecurringTaskService;
use App\Services\Event\RecurringEventService;
use Illuminate\Console\Command;

class ProcessRecurringItems extends Command
{
        protected $signature = 'cc:process-recurring';
    protected $description = 'Process recurring tasks and events, generate next cycle instances';

    public function handle(RecurringTaskService $recurringTaskService, RecurringEventService $recurringEventService): int
    {
        $tasksCreated = $recurringTaskService->generateRecurringTasks();
        $eventsCreated = $recurringEventService->generateRecurringEvents();
        $remindersSent = $recurringEventService->processReminders();

        $this->info("Generated recurring tasks: {$tasksCreated}");
        $this->info("Generated recurring events: {$eventsCreated}");
        $this->info("Sent event reminders: {$remindersSent}");

        return self::SUCCESS;
    }
}
