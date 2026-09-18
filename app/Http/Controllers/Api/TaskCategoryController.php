<?php

namespace App\Http\Controllers\Api;

use App\Models\TaskCategory;
use App\Services\PM\TaskCategoryService;
use Illuminate\Http\Request;

class TaskCategoryController extends BaseApiController
{
    public function __construct(protected TaskCategoryService $taskCategoryService) {}

    public function index(Request $request)
    {
        $categories = $this->taskCategoryService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:191',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->taskCategoryService->create($validated)->load(['creator']),
            '任务分类创建成功',
            201
        );
    }

    public function show(TaskCategory $taskCategory)
    {
        return $this->success($taskCategory->load(['creator']));
    }

    public function update(Request $request, TaskCategory $taskCategory)
    {
        $validated = $request->validate([
            'category_name' => 'sometimes|string|max:191',
            'color' => 'nullable|string|max:20',
        ]);

        $taskCategory = $this->taskCategoryService->update($taskCategory, $validated);

        return $this->success($taskCategory->load(['creator']), 'Updated successfully');
    }

    public function destroy(TaskCategory $taskCategory)
    {
        $this->taskCategoryService->delete($taskCategory);

        return $this->success(null, 'Deleted successfully');
    }
}
