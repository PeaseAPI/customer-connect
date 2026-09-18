<?php

namespace App\Services\PM;

use App\Models\Task;
use App\Enums\TaskStatus;
use App\Enums\Priority;
use Carbon\Carbon;

class RecurringTaskService
{
    /**
     * 生成Recurring task
     * Iterate all due recurring tasks, auto-create next cycle task instance
     */
    public function generateRecurringTasks(): int
    {
        $created = 0;
        $now = now();

        // Find all recurring tasks that need next instance
        $recurringTasks = Task::where('is_recurring', true)
            ->whereNotNull('recurring_next_date')
            ->where('recurring_next_date', '<=', $now->toDateString())
            ->whereNull('parent_task_id') // Only process parent tasks
            ->get();

        foreach ($recurringTasks as $parentTask) {
            $newTask = $this->createNextInstance($parentTask);
            if ($newTask) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * 为Recurring task创建下一 实例
     */
    public function createNextInstance(Task $parentTask): ?Task
    {
        $nextDate = Carbon::parse($parentTask->recurring_next_date);

        // 计算下一次生成日期
        $futureNextDate = $this->calculateNextDate(
            $nextDate,
            $parentTask->recurring_every ?? 1,
            $parentTask->recurring_type ?? 'daily',
            $parentTask->recurring_until ?? null
        );

        // 如果已经超过截止日期，则停止循环
        if ($futureNextDate === null) {
            $parentTask->update(['is_recurring' => false, 'recurring_next_date' => null]);
            return null;
        }

        // 创建新的任务实例
        $newTask = Task::create([
            'company_id' => $parentTask->company_id,
            'project_id' => $parentTask->project_id,
            'title' => $parentTask->title,
            'description' => $parentTask->description,
            'assign_to' => $parentTask->assign_to,
            'status' => TaskStatus::Pending,
            'priority' => $parentTask->priority,
            'start_date' => $nextDate->toDateString(),
            'due_date' => $this->calculateDueDate($nextDate, $parentTask),
            'category_id' => $parentTask->category_id,
            'milestone_id' => $parentTask->milestone_id,
            'parent_task_id' => $parentTask->id,
            'board_column' => $parentTask->board_column,
            'created_by' => $parentTask->created_by,
        ]);

        // 复制标签
        if ($parentTask->labels()->exists()) {
            $newTask->labels()->sync($parentTask->labels->pluck('id'));
        }

        // 复制任务用户
        if ($parentTask->users()->exists()) {
            $newTask->users()->sync($parentTask->users->pluck('id'));
        }

        // 更新父任务的下次生成日期
        $parentTask->update(['recurring_next_date' => $futureNextDate->toDateString()]);

        return $newTask;
    }

    /**
     * 设置任务为Recurring task
     */
    public function setupRecurring(Task $task, array $config): Task
    {
        $task->update([
            'is_recurring' => true,
            'recurring_every' => $config['every'] ?? 1,
            'recurring_type' => $config['type'] ?? 'daily', // daily, weekly, monthly, yearly, custom
            'recurring_until' => $config['until'] ?? null,
            'recurring_next_date' => $this->calculateNextDate(
                now(),
                $config['every'] ?? 1,
                $config['type'] ?? 'daily',
                $config['until'] ?? null
            )?->toDateString(),
        ]);

        return $task->fresh();
    }

    /**
     * 停止Recurring task
     */
    public function stopRecurring(Task $task): Task
    {
        $task->update([
            'is_recurring' => false,
            'recurring_next_date' => null,
        ]);

        return $task->fresh();
    }

    /**
     * 计算下一 循环日期
     */
    private function calculateNextDate(Carbon $currentDate, int $every, string $type, ?string $until = null): ?Carbon
    {
        $nextDate = match ($type) {
            'daily' => $currentDate->copy()->addDays($every),
            'weekly' => $currentDate->copy()->addWeeks($every),
            'monthly' => $currentDate->copy()->addMonths($every),
            'yearly' => $currentDate->copy()->addYears($every),
            default => $currentDate->copy()->addDays($every),
        };

        // 检查YesNo超过截止日期
        if ($until && $nextDate->gt(Carbon::parse($until))) {
            return null;
        }

        return $nextDate;
    }

    /**
     * 根据原始任务的持续时间计算新任务的截止日期
     */
    private function calculateDueDate(Carbon $startDate, Task $originalTask): ?string
    {
        if (!$originalTask->start_date || !$originalTask->due_date) {
            return null;
        }

        $originalStart = Carbon::parse($originalTask->start_date);
        $originalDue = Carbon::parse($originalTask->due_date);
        $durationDays = $originalStart->diffInDays($originalDue);

        return $startDate->copy()->addDays($durationDays)->toDateString();
    }
}
