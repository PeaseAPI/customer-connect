<?php

namespace App\Http\Controllers\Api;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Services\Discussion\DiscussionService;
use Illuminate\Http\Request;

class DiscussionController extends BaseApiController
{
    public function __construct(protected DiscussionService $discussionService) {}

    public function index(Request $request)
    {
        $discussions = $this->discussionService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($discussions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:discussion_categories,id',
            'is_pinned' => 'nullable|boolean',
            'is_announcement' => 'nullable|boolean',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['added_by'] = $request->user()->id;
        $discussion = $this->discussionService->create($validated);
        return $this->success($discussion->load(['category', 'creator']), '讨论创建成功', 201);
    }

    public function show(Discussion $discussion)
    {
        return $this->success($discussion->load(['category', 'creator', 'replies.user']));
    }

    public function update(Request $request, Discussion $discussion)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:discussion_categories,id',
            'is_pinned' => 'nullable|boolean',
            'is_locked' => 'nullable|boolean',
        ]);

        $discussion = $this->discussionService->update($discussion, $validated);
        return $this->success($discussion->load(['category', 'creator']), '更新成功');
    }

    public function destroy(Discussion $discussion)
    {
        $this->discussionService->delete($discussion);
        return $this->success(null, '删除成功');
    }

    public function replies(Discussion $discussion)
    {
        return $this->success($discussion->replies()->with('user')->paginate(15));
    }

    public function storeReply(Request $request, Discussion $discussion)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $reply = $this->discussionService->createReply($discussion, [
            ...$validated,
            'user_id' => $request->user()->id,
            'company_id' => $discussion->company_id,
        ]);
        return $this->success($reply->load('user'), '回复添加成功', 201);
    }

    public function markSolution(Discussion $discussion, DiscussionReply $reply)
    {
        $discussion = $this->discussionService->markSolution($discussion, $reply);
        return $this->success($discussion->load(['category', 'creator', 'replies.user']), '已标记为最佳答案');
    }
}
