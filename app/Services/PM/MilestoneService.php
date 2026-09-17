<?php

namespace App\Services\PM;

use App\Models\Milestone;

class MilestoneService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Milestone::with(['project']);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $query->where('milestone_title', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Milestone
    {
        return Milestone::create($data);
    }

    public function update(Milestone $milestone, array $data): Milestone
    {
        $milestone->update($data);
        return $milestone->fresh();
    }

    public function delete(Milestone $milestone): bool
    {
        return $milestone->delete();
    }
}
