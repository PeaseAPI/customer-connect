<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectTemplate;
use App\Models\Project;
use App\Services\PM\ProjectTemplateService;
use Illuminate\Http\Request;

class ProjectTemplateController extends BaseApiController
{
    public function __construct(protected ProjectTemplateService $templateService) {}

    public function index(Request $request)
    {
        $templates = $this->templateService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($templates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'task_structure' => 'nullable|array',
            'milestone_structure' => 'nullable|array',
            'default_member_ids' => 'nullable|array',
            'default_member_ids.*' => 'exists:users,id',
            'category_id' => 'nullable|exists:project_categories,id',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');
        $validated['created_by'] = $request->user()->id;

        $template = $this->templateService->create($validated);

        return $this->success($template->load(['creator', 'category']), 'Project template created', 201);
    }

    public function show(ProjectTemplate $projectTemplate)
    {
        return $this->success($projectTemplate->load(['creator', 'category']));
    }

    public function update(Request $request, ProjectTemplate $projectTemplate)
    {
        $validated = $request->validate([
            'template_name' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
            'task_structure' => 'nullable|array',
            'milestone_structure' => 'nullable|array',
            'default_member_ids' => 'nullable|array',
            'default_member_ids.*' => 'exists:users,id',
            'category_id' => 'nullable|exists:project_categories,id',
        ]);

        $template = $this->templateService->update($projectTemplate, $validated);

        return $this->success($template->load(['creator', 'category']), 'Updated successfully');
    }

    public function destroy(ProjectTemplate $projectTemplate)
    {
        $this->templateService->delete($projectTemplate);
        return $this->success(null, 'Deleted successfully');
    }

    /**
     * Create template from existing project
     */
    public function createFromProject(Request $request, Project $project)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        $template = $this->templateService->createFromProject(
            $project,
            $validated['template_name'],
            $validated['description'] ?? null
        );

        return $this->success($template->load(['creator', 'category']), 'Template created', 201);
    }

    /**
     * Create project from template
     */
    public function createProject(Request $request, ProjectTemplate $projectTemplate)
    {
        $validated = $request->validate([
            'project_name' => 'required|string|max:191',
            'client_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'deadline' => 'nullable|date',
            'budget' => 'nullable|numeric',
            'project_summary' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;

        $project = $this->templateService->createProjectFromTemplate($projectTemplate, $validated);

        return $this->success($project, 'Project created', 201);
    }
}
