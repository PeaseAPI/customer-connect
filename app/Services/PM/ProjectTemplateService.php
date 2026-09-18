<?php

namespace App\Services\PM;

use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Models\ProjectMilestone;
use Illuminate\Support\Facades\DB;

class ProjectTemplateService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ProjectTemplate::with(['creator', 'category']);

        if (!empty($filters['search'])) {
            $query->where('template_name', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): ProjectTemplate
    {
        return ProjectTemplate::create($data);
    }

    public function update(ProjectTemplate $template, array $data): ProjectTemplate
    {
        $template->update($data);
        return $template->fresh();
    }

    public function delete(ProjectTemplate $template): bool
    {
        return $template->delete();
    }

    /**
     * Create template from existing project
     */
    public function createFromProject(Project $project, string $templateName, ?string $description = null): ProjectTemplate
    {
        $taskStructure = $project->tasks()->with(['subTasks', 'labels'])->get()->map(function ($task) {
            return [
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority?->value,
                'category_id' => $task->category_id,
                'milestone_id' => $task->milestone_id,
                'sub_tasks' => $task->subTasks->map(fn($st) => ['title' => $st->title])->toArray(),
                'labels' => $task->labels->pluck('id')->toArray(),
            ];
        })->toArray();

        $milestoneStructure = $project->milestones->map(fn($m) => [
            'milestone_title' => $m->milestone_title,
            'description' => $m->description,
            'due_date' => $m->due_date?->format('Y-m-d'),
        ])->toArray();

        return ProjectTemplate::create([
            'company_id' => $project->company_id,
            'template_name' => $templateName,
            'description' => $description,
            'task_structure' => $taskStructure,
            'milestone_structure' => $milestoneStructure,
            'default_member_ids' => $project->members->pluck('id')->toArray(),
            'category_id' => $project->category_id,
            'created_by' => $project->created_by,
        ]);
    }

    /**
     * Create project from template
     */
    public function createProjectFromTemplate(ProjectTemplate $template, array $projectData): Project
    {
        return DB::transaction(function () use ($template, $projectData) {
            $projectData['company_id'] = $projectData['company_id'] ?? $template->company_id;

            // Create project
            $project = Project::create(collect($projectData)->only([
                'company_id', 'project_name', 'client_id', 'start_date', 'deadline',
                'status', 'priority', 'budget', 'project_summary', 'category_id', 'created_by',
            ])->toArray());

            // 添加默认成员
            $memberIds = $template->default_member_ids ?? [];
            if (!empty($memberIds)) {
                $project->members()->sync($memberIds);
            }

            // 创建里程碑
            $milestoneMap = [];
            foreach ($template->milestone_structure ?? [] as $msData) {
                $milestone = ProjectMilestone::create([
                    'project_id' => $project->id,
                    'company_id' => $project->company_id,
                    'milestone_title' => $msData['milestone_title'],
                    'description' => $msData['description'] ?? null,
                    'due_date' => $msData['due_date'] ?? null,
                ]);
                $milestoneMap[$msData['milestone_title']] = $milestone->id;
            }

            // 创建任务
            foreach ($template->task_structure ?? [] as $taskData) {
                $task = Task::create([
                    'company_id' => $project->company_id,
                    'project_id' => $project->id,
                    'title' => $taskData['title'],
                    'description' => $taskData['description'] ?? null,
                    'priority' => $taskData['priority'] ?? 'medium',
                    'category_id' => $taskData['category_id'] ?? null,
                    'milestone_id' => $milestoneMap[$taskData['milestone_title'] ?? ''] ?? $taskData['milestone_id'] ?? null,
                    'created_by' => $projectData['created_by'] ?? null,
                ]);

                // 创建子任务
                foreach ($taskData['sub_tasks'] ?? [] as $stData) {
                    \App\Models\SubTask::create([
                        'task_id' => $task->id,
                        'title' => $stData['title'],
                    ]);
                }

                // 同步标签
                if (!empty($taskData['labels'])) {
                    $task->labels()->sync($taskData['labels']);
                }
            }

            return $project->load(['members', 'tasks', 'milestones']);
        });
    }
}
