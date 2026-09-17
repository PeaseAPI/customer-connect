<?php

namespace App\Services\PM;

use App\Models\ProjectMilestone;

class ProjectMilestoneService
{
    public function list(int $projectId, int $perPage = 15)
    {
        return ProjectMilestone::where('project_id', $projectId)
            ->with(['creator'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): ProjectMilestone
    {
        return ProjectMilestone::create($data);
    }

    public function update(ProjectMilestone $milestone, array $data): ProjectMilestone
    {
        $milestone->update($data);
        return $milestone->fresh();
    }

    public function delete(ProjectMilestone $milestone): bool
    {
        return $milestone->delete();
    }
}
