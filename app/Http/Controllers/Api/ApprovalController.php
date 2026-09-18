<?php

namespace App\Http\Controllers\Api;

use App\Models\ApprovalRequest;
use App\Services\Approval\ApprovalControllerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends BaseApiController
{
    public function __construct(protected ApprovalControllerService $approvalService) {}

    public function index(Request $request)
    {
        $requests = $this->approvalService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($requests);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'flow_id' => 'required|exists:approval_flows,id',
            'form_data' => 'required|array',
        ]);
        $validated['user_id'] = Auth::id();
        $validated['company_id'] = $request->attributes->get('company_id');
        $approval = $this->approvalService->create($validated);
        return $this->success($approval, 'Approval request submitted successfully', 201);
    }

    public function show(ApprovalRequest $approval)
    {
        return $this->success($approval->load(['flow', 'user', 'records.approver']));
    }

    public function pending()
    {
        $pending = $this->approvalService->getPendingForUser(Auth::id());
        return $this->paginated($pending);
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        $this->approvalService->approve($approvalRequest, $request->remark);
        return $this->success(null, 'Approval approved');
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        $this->approvalService->reject($approvalRequest, $request->remark);
        return $this->success(null, 'Declined');
    }
}
