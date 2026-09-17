<?php

namespace App\Services\PM;

use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectTimeLogService
{
    public function list(int $projectId, int $perPage = 15)
    {
        return ProjectTimeLog::where('project_id', $projectId)
            ->with(['user', 'task', 'creator', 'breaks'])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): ProjectTimeLog
    {
        return ProjectTimeLog::create($data);
    }

    public function update(ProjectTimeLog $timeLog, array $data): ProjectTimeLog
    {
        $timeLog->update($data);
        return $timeLog->fresh();
    }

    public function delete(ProjectTimeLog $timeLog): bool
    {
        return $timeLog->delete();
    }

    public function startBreak(ProjectTimeLog $timeLog, int $companyId): ProjectTimeLogBreak
    {
        return $timeLog->breaks()->create([
            'company_id' => $companyId,
            'break_start' => now()->format('H:i'),
        ]);
    }

    public function endBreak(ProjectTimeLogBreak $breakLog): ProjectTimeLogBreak
    {
        $breakStart = $breakLog->break_start ? Carbon::parse($breakLog->break_start) : now();
        $breakLog->update([
            'break_end' => now()->format('H:i'),
            'break_minutes' => now()->diffInMinutes($breakStart),
        ]);
        return $breakLog->fresh();
    }
}
