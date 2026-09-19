<?php

namespace App\Http\Controllers\Api;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Services\Discussion\DiscussionService;
use App\Events\DiscussionCreated;
use App\Events\DiscussionReplyAdded;
use App\Services\ContentSecurity\ContentSecurityManager;
use Illuminate\Http\Request;

class DiscussionController extends BaseApiController
{
    public function __construct(
        protected DiscussionService $discussionService,
        protected ContentSecurityManager $auditService,
    ) {}

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

        // 内容审核
        $content = ($validated['title'] ?? '') . ' ' . ($validated['description'] ?? '');
        $auditResult = $this->auditService->auditText($content, 'Discussion', null);
        if (!$auditResult['passed']) {
            return $this->error('内容审核未通过：' . ($auditResult['message'] ?? '内容违规'), 422);
        }

        $discussion = $this->discussionService->create($validated);
        event(new DiscussionCreated($discussion));
        return $this->success($discussion->load(['category', 'creator']), 'Discussion created successfully', 201);
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
        return $this->success($discussion->load(['category', 'creator']), 'Updated successfully');
    }

    public function destroy(Discussion $discussion)
    {
        $this->discussionService->delete($discussion);
        return $this->success(null, 'Deleted successfully');
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
        return $this->success($reply->load('user'), 'Reply added successfully', 201);
    }

    public function markSolution(Discussion $discussion, DiscussionReply $reply)
    {
        $discussion = $this->discussionService->markSolution($discussion, $reply);
        return $this->success($discussion->load(['category', 'creator', 'replies.user']), 'Marked as best answer');
    }
}
