<?php

namespace App\Services\PM;

use App\Models\TaskCategory;

class TaskCategoryService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = TaskCategory::with(['creator']);

        if (!empty($filters['search'])) {
            $query->where('category_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): TaskCategory
    {
        return TaskCategory::create($data);
    }

    public function update(TaskCategory $taskCategory, array $data): TaskCategory
    {
        $taskCategory->update($data);
        return $taskCategory->fresh();
    }

    public function delete(TaskCategory $taskCategory): bool
    {
        return $taskCategory->delete();
    }
}
