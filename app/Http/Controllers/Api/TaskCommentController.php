<?php

namespace App\Http\Controllers\Api;

use App\Models\TaskComment;
use App\Services\PM\TaskCommentService;
use Illuminate\Http\Request;

class TaskCommentController extends BaseApiController
{
    public function __construct(protected TaskCommentService $taskCommentService) {}

    public function index(Request $request, $taskId)
    {
        $comments = $this->taskCommentService->list($taskId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($comments);
    }

    public function store(Request $request, $taskId)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $validated['task_id'] = $taskId;
        $validated['user_id'] = $request->user()->id;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->taskCommentService->create($validated)->load(['user', 'creator']),
            '任务评论创建成功',
            201
        );
    }

    public function show($taskId, TaskComment $comment)
    {
        return $this->success($comment->load(['user', 'creator']));
    }

    public function update(Request $request, $taskId, TaskComment $comment)
    {
        $validated = $request->validate([
            'comment' => 'sometimes|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $comment = $this->taskCommentService->update($comment, $validated);

        return $this->success($comment->load(['user', 'creator']), 'Updated successfully');
    }

    public function destroy($taskId, TaskComment $comment)
    {
        $this->taskCommentService->delete($comment);

        return $this->success(null, 'Deleted successfully');
    }
}

