<?php

namespace App\Console\Commands;

use App\Services\PM\RecurringTaskService;
use App\Services\Event\RecurringEventService;
use Illuminate\Console\Command;

class ProcessRecurringItems extends Command
{
    protected $signature = 'kht:process-recurring';
    protected $description = '处理循环任务和循环事件，生成下一周期的实例';

    public function handle(RecurringTaskService $recurringTaskService, RecurringEventService $recurringEventService): int
    {
        $tasksCreated = $recurringTaskService->generateRecurringTasks();
        $eventsCreated = $recurringEventService->generateRecurringEvents();
        $remindersSent = $recurringEventService->processReminders();

        $this->info("生成循环任务: {$tasksCreated} 个");
        $this->info("生成循环事件: {$eventsCreated} 个");
        $this->info("发送事件提醒: {$remindersSent} 个");

        return self::SUCCESS;
    }
}
