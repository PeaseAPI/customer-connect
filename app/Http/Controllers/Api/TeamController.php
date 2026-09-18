<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\AddTeamMembersRequest;
use App\Models\Team;
use App\Services\HRM\TeamService;
use Illuminate\Http\Request;

class TeamController extends BaseApiController
{
    public function __construct(protected TeamService $teamService) {}

    public function index(Request $request)
    {
        $teams = $this->teamService->list($request->per_page ?? 15);
        return $this->paginated($teams);
    }

    public function store(StoreTeamRequest $request)
    {
        $v = $request->validated();
        $v['added_by'] = $request->user()->id;
        $memberIds = $v['member_ids'] ?? [];
        unset($v['member_ids']);
        $team = $this->teamService->create($v, $memberIds);
        return $this->success($team->load(['creator', 'members']), 'Team created successfully', 201);
    }

    public function show(Team $team)
    {
        return $this->success($team->load(['creator', 'members']));
    }

    public function update(Request $request, Team $team)
    {
        $v = $request->validate([
            'team_name' => 'sometimes|string|max:255|unique:teams,team_name,' . $team->id,
        ]);
        $team = $this->teamService->update($team, $v);
        return $this->success($team->load(['creator', 'members']), 'Updated successfully');
    }

    public function destroy(Team $team)
    {
        $this->teamService->delete($team);
        return $this->success(null, 'Deleted successfully');
    }

    public function addMembers(AddTeamMembersRequest $request, Team $team)
    {
        $v = $request->validated();
        $team = $this->teamService->addMembers($team, $v['member_ids']);
        return $this->success($team->load(['creator', 'members']), 'Member added successfully');
    }

    public function removeMember(Request $request, Team $team, $userId)
    {
        $team = $this->teamService->removeMember($team, $userId);
        return $this->success($team->load(['creator', 'members']), 'Member removed successfully');
    }
}
