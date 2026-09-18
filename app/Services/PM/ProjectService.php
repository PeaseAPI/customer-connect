<?php

namespace App\Services\PM;

use App\Models\Project;
use App\Models\Task;
use App\Events\TaskAssigned;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Project::with(['client', 'creator', 'members', 'tasks']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        if (!empty($filters['search'])) {
            $query->where('project_name', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $members = $data['members'] ?? [];
            unset($data['members']);

            $project = Project::create($data);
            if (!empty($members)) {
                $project->members()->sync($members);
            }
            return $project;
        });
    }

    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            // Status changes must go through changeStatus()
            unset($data['status']);

            $members = $data['members'] ?? null;
            unset($data['members']);

            $project->update($data);
            if ($members !== null) {
                $project->members()->sync($members);
            }
            return $project->fresh();
        });
    }

    public function changeStatus(Project $project, string $status): Project
    {
        $project->update(['status' => $status]);
        return $project->fresh();
    }

    public function delete(Project $project): bool
    {
        return $project->delete();
    }

    public function addMember(Project $project, int $userId, string $role = 'member'): void
    {
        $project->members()->syncWithoutDetaching([
            $userId => ['role' => $role, 'company_id' => $project->company_id],
        ]);
    }

    public function removeMember(Project $project, int $userId): void
    {
        $project->members()->detach($userId);
    }

    public function getProgress(Project $project): float
    {
        $total = $project->tasks()->count();
        if ($total === 0) return 0;
        $completed = $project->tasks()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 2);
    }

        public function getStatistics(Project $project): array
    {
        $tasks = $project->tasks;
        return [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'overdue_tasks' => $tasks->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'total_hours' => $tasks->sum('estimate_hours'),
            'logged_hours' => $project->timelogs()->sum('total_hours'),
            'progress' => $this->getProgress($project),
        ];
    }
}
