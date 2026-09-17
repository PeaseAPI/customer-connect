<?php

namespace App\Services\PM;

use App\Models\Timelog;
use Illuminate\Support\Facades\DB;

class TimelogService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Timelog::with(['user', 'task', 'project']);

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['task_id'])) {
            $query->where('task_id', $filters['task_id']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['date'])) {
            $query->whereDate('start_time', $filters['date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Timelog
    {
        return Timelog::create($data);
    }

    public function update(Timelog $timelog, array $data): Timelog
    {
        $timelog->update($data);
        return $timelog->fresh();
    }

    public function delete(Timelog $timelog): bool
    {
        return $timelog->delete();
    }
}
