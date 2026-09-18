<?php

namespace App\Http\Controllers\Api;

use App\Models\LeadFollowUp;
use App\Services\CRM\LeadFollowUpService;
use Illuminate\Http\Request;

class LeadFollowUpController extends BaseApiController
{
    public function __construct(protected LeadFollowUpService $leadFollowUpService) {}

    public function index(Request $request, $leadId)
    {
        $followUps = $this->leadFollowUpService->list($leadId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($followUps);
    }

    public function store(Request $request, $leadId)
    {
        $validated = $request->validate([
            'follow_up_date' => 'required|date',
            'follow_up_type' => 'nullable|string|max:50',
            'remark' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
            'status' => 'nullable|in:pending,completed,cancelled',
        ]);

        $validated['lead_id'] = $leadId;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->leadFollowUpService->create($validated)->load(['lead', 'addedBy']),
            '跟进创建成功',
            201
        );
    }

    public function update(Request $request, $leadId, LeadFollowUp $followUp)
    {
        $validated = $request->validate([
            'follow_up_date' => 'sometimes|date',
            'follow_up_type' => 'nullable|string|max:50',
            'remark' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
            'status' => 'sometimes|in:pending,completed,cancelled',
        ]);

        $followUp = $this->leadFollowUpService->update($followUp, $validated);

        return $this->success($followUp->load(['lead', 'addedBy']), 'Updated successfully');
    }

    public function destroy($leadId, LeadFollowUp $followUp)
    {
        $this->leadFollowUpService->delete($followUp);

        return $this->success(null, 'Deleted successfully');
    }
}
