<?php

namespace App\Services\PM;

use App\Models\ProjectCategory;

class ProjectCategoryService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ProjectCategory::with(['creator']);

        if (!empty($filters['search'])) {
            $query->where('category_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ProjectCategory
    {
        return ProjectCategory::create($data);
    }

    public function update(ProjectCategory $projectCategory, array $data): ProjectCategory
    {
        $projectCategory->update($data);
        return $projectCategory->fresh();
    }

    public function delete(ProjectCategory $projectCategory): bool
    {
        return $projectCategory->delete();
    }
}
