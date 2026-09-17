<?php

namespace App\Http\Controllers\Api;

use App\Models\Notice;
use App\Services\Notice\NoticeService;
use Illuminate\Http\Request;

class NoticeController extends BaseApiController
{
    public function __construct(protected NoticeService $noticeService) {}

    public function index(Request $request)
    {
        $notices = $this->noticeService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($notices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'heading' => 'required|string|max:191',
            'description' => 'nullable|string',
            'to' => 'nullable|string|max:20',
            'notice_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:notice_date',
        ]);

        $validated['added_by'] = $request->user()->id;

        $notice = $this->noticeService->create($validated);
        return $this->success($notice->load('creator'), '公告创建成功', 201);
    }

    public function show(Notice $notice)
    {
        return $this->success($notice->load(['creator', 'views.user']));
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'heading' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
            'to' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
            'expiry_date' => 'nullable|date',
        ]);

        $notice = $this->noticeService->update($notice, $validated);
        return $this->success($notice->load('creator'), '更新成功');
    }

    public function destroy(Notice $notice)
    {
        $this->noticeService->delete($notice);
        return $this->success(null, '删除成功');
    }

    public function markAsRead(Notice $notice, Request $request)
    {
        $this->noticeService->markAsRead($notice, $request->user()->id);
        return $this->success(null, '已标记为已读');
    }
}
