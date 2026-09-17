<?php

namespace App\Services\HRM;

use App\Models\Team;
use App\Models\EmployeeTeam;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function list(int $perPage = 15)
    {
        return Team::with(['creator', 'members'])->latest()->paginate($perPage);
    }

    public function create(array $data, array $memberIds = []): Team
    {
        return DB::transaction(function () use ($data, $memberIds) {
            $team = Team::create($data);
            $this->attachMembers($team, $memberIds);
            return $team;
        });
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);
        return $team->fresh();
    }

    public function delete(Team $team): bool
    {
        return DB::transaction(function () use ($team) {
            EmployeeTeam::where('team_id', $team->id)->delete();
            return $team->delete();
        });
    }

    public function addMembers(Team $team, array $memberIds): Team
    {
        $this->attachMembers($team, $memberIds);
        return $team->fresh();
    }

    public function removeMember(Team $team, int $userId): Team
    {
        EmployeeTeam::where('team_id', $team->id)->where('user_id', $userId)->delete();
        return $team->fresh();
    }

    protected function attachMembers(Team $team, array $memberIds): void
    {
        foreach ($memberIds as $userId) {
            EmployeeTeam::firstOrCreate([
                'team_id' => $team->id,
                'user_id' => $userId,
                'company_id' => $team->company_id,
            ]);
        }
    }
}
