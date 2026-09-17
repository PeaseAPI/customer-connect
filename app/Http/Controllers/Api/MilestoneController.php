<?php

namespace App\Http\Controllers\Api;

use App\Models\Milestone;
use App\Services\PM\MilestoneService;
use Illuminate\Http\Request;

class MilestoneController extends BaseApiController
{
    public function __construct(protected MilestoneService $milestoneService) {}

    public function index(Request $request)
    {
        $milestones = $this->milestoneService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($milestones);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'milestone_title' => 'required|string|max:191',
            'milestone_cost' => 'nullable|numeric',
            'status' => 'nullable|in:incomplete,complete',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->milestoneService->create($validated)->load(['project']),
            '里程碑创建成功',
            201
        );
    }

    public function show(Milestone $milestone)
    {
        return $this->success($milestone->load(['project', 'tasks']));
    }

    public function update(Request $request, Milestone $milestone)
    {
        $validated = $request->validate([
            'milestone_title' => 'sometimes|string|max:191',
            'milestone_cost' => 'nullable|numeric',
            'status' => 'nullable|in:incomplete,complete',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
        ]);

        $milestone = $this->milestoneService->update($milestone, $validated);

        return $this->success($milestone->load(['project']), '更新成功');
    }

    public function destroy(Milestone $milestone)
    {
        $this->milestoneService->delete($milestone);

        return $this->success(null, '删除成功');
    }
}
