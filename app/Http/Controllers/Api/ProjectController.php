<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Services\PM\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends BaseApiController
{
    public function __construct(protected ProjectService $projectService) {}

    public function index(Request $request)
    {
        $projects = $this->projectService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($projects);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:191',
            'client_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|in:not_started,planning,in_progress,on_hold,completed,canceled,finished',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'budget' => 'nullable|numeric',
            'project_summary' => 'nullable|string',
            'category_id' => 'nullable|exists:project_categories,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success($this->projectService->create($validated), 'Project created', 201);
    }

    public function show(Project $project)
    {
        return $this->success($project->load(['client', 'members', 'tasks', 'milestones', 'creator']));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_name' => 'sometimes|string|max:191',
            'client_id' => 'nullable|exists:users,id',
            'start_date' => 'sometimes|date',
            'deadline' => 'nullable|date',
            'status' => 'sometimes|in:not_started,planning,in_progress,on_hold,completed,canceled,finished',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'budget' => 'nullable|numeric',
            'project_summary' => 'nullable|string',
            'category_id' => 'nullable|exists:project_categories,id',
        ]);

        $status = $validated['status'] ?? null;
        unset($validated['status']);

        $validated['last_updated_by'] = $request->user()->id;

        if (!empty($validated)) {
            $project = $this->projectService->update($project, $validated);
        }

        if ($status) {
            $project = $this->projectService->changeStatus($project, $status);
        }

        return $this->success($project->load(['client', 'members', 'creator']), 'Updated successfully');
    }

    public function destroy(Project $project)
    {
        $this->projectService->delete($project);
        return $this->success(null, 'Deleted successfully');
    }

    public function addMember(Request $request, Project $project)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:manager,member,viewer',
        ]);

        $this->projectService->addMember($project, $validated['user_id'], $validated['role'] ?? 'member');
        return $this->success(null, '成员添加成功');
    }

    public function removeMember(Project $project, $user)
    {
        $this->projectService->removeMember($project, $user);
        return $this->success(null, '成员移除成功');
    }
}
