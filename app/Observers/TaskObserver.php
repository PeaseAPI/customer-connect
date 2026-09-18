<?php

namespace App\Observers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\Project;

class TaskObserver
{
    public function created(Task $task): void
    {
        $this->updateProjectProgress($task->project);
    }

    public function updated(Task $task): void
    {
        if ($task->isDirty('status')) {
            $this->updateProjectProgress($task->project);
        }
    }

    public function deleted(Task $task): void
    {
        $this->updateProjectProgress($task->project);
    }

    private function updateProjectProgress(?Project $project): void
    {
        if (!$project) return;

        $total = $project->tasks()->count();
        $completed = $project->tasks()->where('status', TaskStatus::Completed)->count();

        $progress = $total > 0 ? round(($completed / $total) * 100, 2) : 0;
        $project->update(['progress' => $progress]);

        // 如果所有任务完成，自动更新项目状态
        if ($total > 0 && $completed === $total) {
            $project->update(['status' => ProjectStatus::Completed]);
        } elseif ($project->status === ProjectStatus::Completed && $completed < $total) {
            $project->update(['status' => ProjectStatus::InProgress]);
        }
    }
}
