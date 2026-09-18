<?php

namespace App\Http\Controllers\Api;

use App\Models\SubTask;
use App\Models\Task;
use App\Services\PM\SubTaskService;
use Illuminate\Http\Request;

class SubTaskController extends BaseApiController
{
    public function __construct(protected SubTaskService $subTaskService) {}

    public function index(Request $request, Task $task)
    {
        $subTasks = $this->subTaskService->list($task, $request->all(), $request->per_page ?? 15);

        return $this->paginated($subTasks);
    }

    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:incomplete,complete',
        ]);

        $validated['added_by'] = $request->user()->id;
        $subTask = $this->subTaskService->create($task, $validated);

        return $this->success($subTask->load(['assignee', 'creator']), 'Sub-task created successfully', 201);
    }

    public function show(Task $task, SubTask $subTask)
    {
        return $this->success($subTask->load(['assignee', 'creator']));
    }

    public function update(Request $request, Task $task, SubTask $subTask)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:incomplete,complete',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $subTask = $this->subTaskService->update($subTask, $validated);

        return $this->success($subTask->load(['assignee', 'creator']), 'Updated successfully');
    }

    public function destroy(Task $task, SubTask $subTask)
    {
        $this->subTaskService->delete($subTask);

        return $this->success(null, 'Deleted successfully');
    }
}
