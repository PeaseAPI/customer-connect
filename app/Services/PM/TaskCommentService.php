<?php

namespace App\Services\PM;

use App\Models\TaskComment;

class TaskCommentService
{
    public function list(int $taskId, array $filters = [], int $perPage = 15)
    {
        $query = TaskComment::where('task_id', $taskId)
            ->with(['user', 'creator']);

        if (!empty($filters['search'])) {
            $query->where('comment', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): TaskComment
    {
        return TaskComment::create($data);
    }

    public function update(TaskComment $comment, array $data): TaskComment
    {
        $comment->update($data);
        return $comment->fresh();
    }

    public function delete(TaskComment $comment): bool
    {
        return $comment->delete();
    }
}

