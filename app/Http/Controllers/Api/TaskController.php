<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Services\PM\TaskService;
use Illuminate\Http\Request;

class TaskController extends BaseApiController
{
    public function __construct(protected TaskService $taskService) {}

    public function index(Request $request, $projectId = null)
    {
        $tasks = $this->taskService->list(
            array_merge($request->all(), $projectId ? ['project_id' => $projectId] : []),
            $request->per_page ?? 15
        );
        return $this->paginated($tasks);
    }

        public function store(Request $request, $projectId = null)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'assign_to' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'status' => 'sometimes|in:pending,in_progress,review,completed,cancelled',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'category_id' => 'nullable|exists:task_categories,id',
            'board_column' => 'nullable|integer',
            'is_pinned' => 'sometimes|boolean',
            'milestone_id' => 'nullable|exists:milestones,id',
            // 循环任务
            'is_recurring' => 'sometimes|boolean',
            'recurring_every' => 'nullable|integer|min:1',
            'recurring_type' => 'nullable|in:daily,weekly,monthly,yearly,custom',
            'recurring_until' => 'nullable|date|after:due_date',
            // 标签
            'label_ids' => 'nullable|array',
            'label_ids.*' => 'exists:task_labels,id',
        ]);

        if ($projectId) {
            $validated['project_id'] = $projectId;
        }

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        $labelIds = $validated['label_ids'] ?? [];
        unset($validated['label_ids']);

        $task = $this->taskService->create($validated);

        // 同步标签
        if (!empty($labelIds)) {
            $task->labels()->sync($labelIds);
        }

        // 如果是循环任务，设置下次生成日期
        if (!empty($validated['is_recurring'])) {
            app(\App\Services\PM\RecurringTaskService::class)->setupRecurring($task, [
                'every' => $validated['recurring_every'] ?? 1,
                'type' => $validated['recurring_type'] ?? 'daily',
                'until' => $validated['recurring_until'] ?? null,
            ]);
        }

        return $this->success($task->load(['assignee', 'project', 'creator', 'labels']), '任务创建成功', 201);
    }

    public function show($projectId, Task $task)
    {
        return $this->success($task->load(['assignee', 'project', 'subTasks', 'labels', 'creator', 'comments.user']));
    }

    public function update(Request $request, $projectId, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
            'assign_to' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:pending,in_progress,review,completed,cancelled',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'category_id' => 'nullable|exists:task_categories,id',
            'board_column' => 'nullable|integer',
        ]);

        if (isset($validated['status'])) {
            $newStatus = $validated['status'];
            unset($validated['status']);
            $task = $this->taskService->changeStatus($task, $newStatus);
        }

        if (isset($validated['assign_to'])) {
            $newAssignee = $validated['assign_to'];
            unset($validated['assign_to']);
            $task = $this->taskService->assign($task, (int) $newAssignee);
        }

        if (!empty($validated)) {
            $task = $this->taskService->update($task, $validated);
        }

        return $this->success($task->load(['assignee', 'project', 'creator']), '更新成功');
    }

    public function destroy($projectId, Task $task)
    {
        $this->taskService->delete($task);
        return $this->success(null, '删除成功');
    }

        public function reorder(Request $request)
    {
        $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
        ]);

        $this->taskService->reorder($request->tasks);

        return $this->success(null, '排序更新成功');
    }

    /**
     * 任务Pin切换
     */
    public function togglePin(Task $task)
    {
        $task->update(['is_pinned' => !$task->is_pinned]);
        return $this->success($task->fresh(), $task->is_pinned ? '任务已置顶' : '任务已取消置顶');
    }

    /**
     * 任务日历视图 — 按日期范围查询
     */
    public function calendar(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'project_id' => 'nullable|exists:projects,id',
            'assignee_id' => 'nullable|exists:users,id',
        ]);

        $query = Task::with(['project', 'assignee', 'creator'])
            ->whereNotNull('due_date')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('due_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_date', '<=', $validated['start_date'])
                         ->where('due_date', '>=', $validated['end_date']);
                  });
            });

        if (!empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }
        if (!empty($validated['assignee_id'])) {
            $query->where('assign_to', $validated['assignee_id']);
        }

        $tasks = $query->orderBy('due_date')->get();

        return $this->success($tasks, '获取成功');
    }

    /**
     * 上传任务文件
     */
    public function uploadFile(Request $request, Task $task)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240',
            'description' => 'nullable|string|max:500',
        ]);

        $uploadedFile = $validated['file'];
        $path = $uploadedFile->store("task_files/{$task->id}", 'local');

        $file = $task->files()->create([
            'company_id' => $task->company_id,
            'user_id' => $request->user()->id,
            'filename' => $uploadedFile->getClientOriginalName(),
            'hashname' => basename($path),
            'size' => $uploadedFile->getSize(),
            'disk' => 'local',
            'path' => $path,
        ]);

        return $this->success($file, '文件上传成功', 201);
    }

    /**
     * 获取任务文件列表
     */
    public function listFiles(Task $task)
    {
        return $this->success($task->files()->with('user')->orderBy('created_at', 'desc')->get(), '获取成功');
    }

    /**
     * 删除任务文件
     */
    public function deleteFile(Task $task, $fileId)
    {
        $file = $task->files()->where('id', $fileId)->first();
        if (!$file) {
            return $this->error('文件不存在', 404);
        }

        \Illuminate\Support\Facades\Storage::disk($file->disk)->delete($file->path);
        $file->delete();

        return $this->success(null, '文件删除成功');
    }
}
