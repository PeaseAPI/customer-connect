<?php

namespace App\Http\Controllers\Api;

use App\Models\KnowledgeBaseCategory;
use Illuminate\Http\Request;

class KnowledgeBaseCategoryController extends BaseApiController
{
    public function index(Request $request)
    {
        return $this->paginated(KnowledgeBaseCategory::query()->latest()->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);
        $data = $request->all();
        $data['company_id'] = app('App\Services\ContextService')->getCompanyId();

        return $this->success(KnowledgeBaseCategory::create($data), 'Created', 201);
    }

    public function show(KnowledgeBaseCategory $knowledgeBaseCategory)
    {
        return $this->success($knowledgeBaseCategory);
    }

    public function update(Request $request, KnowledgeBaseCategory $knowledgeBaseCategory)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);
        $knowledgeBaseCategory->update($request->all());

        return $this->success($knowledgeBaseCategory, 'Updated');
    }

    public function destroy(KnowledgeBaseCategory $knowledgeBaseCategory)
    {
        $knowledgeBaseCategory->delete();

        return $this->success(null, 'Deleted');
    }
}
