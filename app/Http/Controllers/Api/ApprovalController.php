<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApprovalStatus;
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

    /**
     * 更新待审批的申请 (仅 pending 状态可改)
     */
    public function update(Request $request, ApprovalRequest $approval)
    {
        if ($approval->status !== ApprovalStatus::Pending) {
            return $this->error('Only pending approval requests can be updated', 422);
        }

        $validated = $request->validate([
            'form_data' => 'required|array',
        ]);

        $approval->update(['form_data' => $validated['form_data']]);

        return $this->success($approval->fresh(), 'Approval request updated');
    }

    /**
     * 撤回/删除待审批的申请 (仅 pending 状态可删)
     */
    public function destroy(ApprovalRequest $approval)
    {
        if ($approval->status !== ApprovalStatus::Pending) {
            return $this->error('Only pending approval requests can be deleted', 422);
        }

        $approval->delete();

        return $this->success(null, 'Approval request deleted');
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

    /**
     * DingTalk approval callback (webhook)
     */
    public function dingtalkCallback(Request $request)
    {
        $this->approvalService->handleCallback('dingtalk', $request->all());
        return response()->json(['success' => true]);
    }

    /**
     * WeWork (企业微信) approval callback (webhook)
     */
    public function weworkCallback(Request $request)
    {
        $this->approvalService->handleCallback('wework', $request->all());
        return response()->json(['success' => true]);
    }

    /**
     * Feishu (飞书) approval callback (webhook)
     */
    public function feishuCallback(Request $request)
    {
        $this->approvalService->handleCallback('feishu', $request->all());
        return response()->json(['success' => true]);
    }
}
