<?php

namespace App\Http\Controllers\Api;

use App\Models\KnowledgeBase;
use App\Services\Company\KnowledgeBaseService;
use Illuminate\Http\Request;

class KnowledgeBaseController extends BaseApiController
{
    public function __construct(protected KnowledgeBaseService $knowledgeBaseService) {}

    public function index(Request $request)
    {
        $articles = $this->knowledgeBaseService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($articles);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:knowledge_base_categories,id',
            'status' => 'nullable|in:active,inactive',
        ]);
                $v['added_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->knowledgeBaseService->create($v)->load(['category', 'creator']), '知识库文章创建成功', 201);
    }

    public function show(KnowledgeBase $knowledgeBase) { return $this->success($knowledgeBase->load(['category', 'creator'])); }

    public function update(Request $request, KnowledgeBase $knowledgeBase)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:knowledge_base_categories,id',
            'status' => 'nullable|in:active,inactive',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $knowledgeBase = $this->knowledgeBaseService->update($knowledgeBase, $validated);

        return $this->success($knowledgeBase->load(['category', 'creator']), 'Updated successfully');
    }

    public function destroy(KnowledgeBase $knowledgeBase)
    {
        $this->knowledgeBaseService->delete($knowledgeBase);
        return $this->success(null, 'Deleted successfully');
    }
}
