<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProjectMilestoneRequest;
use App\Http\Requests\UpdateProjectMilestoneRequest;
use App\Models\ProjectMilestone;
use App\Services\PM\ProjectMilestoneService;
use Illuminate\Http\Request;

class ProjectMilestoneController extends BaseApiController
{
    public function __construct(protected ProjectMilestoneService $projectMilestoneService) {}

    public function index(Request $request, $projectId)
    {
        $milestones = $this->projectMilestoneService->list($projectId, $request->per_page ?? 15);
        return $this->paginated($milestones);
    }

    public function store(StoreProjectMilestoneRequest $request, $projectId)
    {
        $v = $request->validated();
        $v['project_id'] = $projectId;
        $v['added_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->projectMilestoneService->create($v)->load(['creator']), '里程碑创建成功', 201);
    }

    public function show($projectId, ProjectMilestone $milestone)
    {
        return $this->success($milestone->load(['creator']));
    }

    public function update(UpdateProjectMilestoneRequest $request, $projectId, ProjectMilestone $milestone)
    {
        $v = $request->validated();
        $milestone = $this->projectMilestoneService->update($milestone, $v);
        return $this->success($milestone->load(['creator']), 'Updated successfully');
    }

    public function destroy($projectId, ProjectMilestone $milestone)
    {
        $this->projectMilestoneService->delete($milestone);
        return $this->success(null, 'Deleted successfully');
    }
}
