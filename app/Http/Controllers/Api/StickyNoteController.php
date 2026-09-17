<?php

namespace App\Http\Controllers\Api;

use App\Models\StickyNote;
use App\Services\Company\StickyNoteService;
use Illuminate\Http\Request;

class StickyNoteController extends BaseApiController
{
    public function __construct(protected StickyNoteService $stickyNoteService) {}

    public function index(Request $request)
    {
        $notes = $this->stickyNoteService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($notes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'note_text' => 'required|string',
            'color' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->stickyNoteService->create($validated)->load(['user', 'creator']),
            '便签创建成功',
            201
        );
    }

    public function show(StickyNote $stickyNote)
    {
        return $this->success($stickyNote->load(['user', 'creator']));
    }

    public function update(Request $request, StickyNote $stickyNote)
    {
        $validated = $request->validate([
            'note_text' => 'sometimes|string',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $stickyNote = $this->stickyNoteService->update($stickyNote, $validated);

        return $this->success($stickyNote->load(['user', 'creator']), '更新成功');
    }

    public function destroy(StickyNote $stickyNote)
    {
        $this->stickyNoteService->delete($stickyNote);

        return $this->success(null, '删除成功');
    }
}
