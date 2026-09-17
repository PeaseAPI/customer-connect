<?php

namespace App\Services\PM;

use App\Models\SubTask;
use App\Models\Task;

class SubTaskService
{
    public function list(Task $task, array $filters = [], int $perPage = 15)
    {
        $query = $task->subTaskItems();

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(Task $task, array $data): SubTask
    {
        return $task->subTaskItems()->create(array_merge($data, [
            'company_id' => $task->company_id,
        ]));
    }

    public function update(SubTask $subTask, array $data): SubTask
    {
        $subTask->update($data);
        return $subTask->fresh();
    }

    public function delete(SubTask $subTask): bool
    {
        return $subTask->delete();
    }
}
