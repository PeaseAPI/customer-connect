<?php

namespace App\Http\Controllers\Api;

use App\Models\DiscussionCategory;
use Illuminate\Http\Request;

class DiscussionCategoryController extends BaseApiController
{
    public function index(Request $request)
    {
        return $this->paginated(DiscussionCategory::query()->latest()->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $data = $request->all();
        $data['company_id'] = app('App\Services\ContextService')->getCompanyId();

        return $this->success(DiscussionCategory::create($data), 'Created', 201);
    }

    public function show(DiscussionCategory $discussionCategory)
    {
        return $this->success($discussionCategory);
    }

    public function update(Request $request, DiscussionCategory $discussionCategory)
    {
        $request->validate(['name' => 'sometimes|required|string|max:100']);
        $discussionCategory->update($request->all());

        return $this->success($discussionCategory, 'Updated');
    }

    public function destroy(DiscussionCategory $discussionCategory)
    {
        $discussionCategory->delete();

        return $this->success(null, 'Deleted');
    }
}
