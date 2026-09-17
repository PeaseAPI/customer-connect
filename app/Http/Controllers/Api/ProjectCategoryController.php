<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectCategory;
use App\Services\PM\ProjectCategoryService;
use Illuminate\Http\Request;

class ProjectCategoryController extends BaseApiController
{
    public function __construct(protected ProjectCategoryService $projectCategoryService) {}

    public function index(Request $request)
    {
        $categories = $this->projectCategoryService->list($request->all(), $request->per_page ?? 15);

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
            $this->projectCategoryService->create($validated)->load(['creator']),
            '项目分类创建成功',
            201
        );
    }

    public function show(ProjectCategory $projectCategory)
    {
        return $this->success($projectCategory->load(['creator']));
    }

    public function update(Request $request, ProjectCategory $projectCategory)
    {
        $validated = $request->validate([
            'category_name' => 'sometimes|string|max:191',
            'color' => 'nullable|string|max:20',
        ]);

        $projectCategory = $this->projectCategoryService->update($projectCategory, $validated);

        return $this->success($projectCategory->load(['creator']), '更新成功');
    }

    public function destroy(ProjectCategory $projectCategory)
    {
        $this->projectCategoryService->delete($projectCategory);

        return $this->success(null, '删除成功');
    }
}
