<?php

namespace App\Services\PM;

use App\Models\Task;
use App\Models\TaskComment;
use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function list(array $filters = [], int $perPage = 20)
    {
        $query = Task::with(['project', 'assignee', 'creator', 'parentTask']);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['assignee_id'])) {
            $query->where('assign_to', $filters['assignee_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['parent_id'])) {
            $query->where('parent_task_id', $filters['parent_id']);
        }
        if (!empty($filters['keyword'])) {
            $query->where('title', 'like', "%{$filters['keyword']}%");
        }
        if (!empty($filters['overdue'])) {
            $query->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled']);
        }

        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = Task::create($data);
            if (!empty($data['assign_to'])) {
                event(new TaskAssigned($task, $data['assign_to']));
            }
            return $task;
        });
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }

    public function changeStatus(Task $task, string $newStatus): Task
    {
        $oldStatus = $task->status?->value ?? (string) $task->getRawOriginal('status');
        $task->update(['status' => $newStatus]);

        if ($newStatus === 'completed') {
            $task->update(['completed_at' => now()]);
        }

        event(new TaskStatusChanged($task, $oldStatus, $newStatus));
        return $task->fresh();
    }

    public function assign(Task $task, int $assigneeId): Task
    {
        $task->update(['assign_to' => $assigneeId]);
        event(new TaskAssigned($task, $assigneeId));
        return $task->fresh();
    }

    public function addComment(Task $task, array $data): TaskComment
    {
        return $task->comments()->create(array_merge($data, [
            'company_id' => $task->company_id,
        ]));
    }

    public function reorder(array $tasks): void
    {
        $this->updateSortOrder($tasks);
    }

    public function updateSortOrder(array $taskOrders): void
    {
        DB::transaction(function () use ($taskOrders) {
            foreach ($taskOrders as $taskId => $order) {
                Task::where('id', $taskId)->update(['sort_order' => $order]);
            }
        });
    }
}
