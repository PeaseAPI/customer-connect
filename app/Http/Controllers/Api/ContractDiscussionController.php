<?php

namespace App\Http\Controllers\Api;

use App\Models\ContractDiscussion;
use App\Services\Contract\ContractDiscussionService;
use Illuminate\Http\Request;

class ContractDiscussionController extends BaseApiController
{
    public function __construct(protected ContractDiscussionService $contractDiscussionService) {}

    public function index(Request $request, $contractId)
    {
        $discussions = $this->contractDiscussionService->list($contractId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($discussions);
    }

    public function store(Request $request, $contractId)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $validated['contract_id'] = $contractId;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->contractDiscussionService->create($validated)->load(['creator']),
            '讨论创建成功',
            201
        );
    }

    public function show($contractId, ContractDiscussion $discussion)
    {
        return $this->success($discussion->load(['creator']));
    }

    public function update(Request $request, $contractId, ContractDiscussion $discussion)
    {
        $validated = $request->validate([
            'comment' => 'sometimes|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $discussion = $this->contractDiscussionService->update($discussion, $validated);

        return $this->success($discussion->load(['creator']), 'Updated successfully');
    }

    public function destroy($contractId, ContractDiscussion $discussion)
    {
        $this->contractDiscussionService->delete($discussion);

        return $this->success(null, 'Deleted successfully');
    }
}
