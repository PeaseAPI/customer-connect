<?php

namespace App\Services\Approval;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalFlow;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalControllerService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = ApprovalRequest::with(['flow', 'user']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): ApprovalRequest
    {
        return ApprovalRequest::create($data);
    }

    public function approve(ApprovalRequest $approvalRequest, ?string $remark = null): void
    {
        DB::transaction(function () use ($approvalRequest, $remark) {
            ApprovalRecord::where('request_id', $approvalRequest->id)
                ->where('approver_id', Auth::id())
                ->update([
                    'action' => 'approve',
                    'remark' => $remark,
                    'acted_at' => now(),
                ]);

            $nextStep = $approvalRequest->current_step + 1;
            $totalSteps = $approvalRequest->records()->count();

            if ($nextStep >= $totalSteps) {
                $approvalRequest->update(['current_step' => $nextStep, 'status' => ApprovalStatus::Approved]);
            } else {
                $approvalRequest->update(['current_step' => $nextStep]);
            }
        });
    }

    public function reject(ApprovalRequest $approvalRequest, ?string $remark = null): void
    {
        DB::transaction(function () use ($approvalRequest, $remark) {
            ApprovalRecord::where('request_id', $approvalRequest->id)
                ->where('approver_id', Auth::id())
                ->update([
                    'action' => 'reject',
                    'remark' => $remark,
                    'acted_at' => now(),
                ]);

            $approvalRequest->update(['status' => ApprovalStatus::Rejected]);
        });
    }

    public function getPendingForUser(int $userId, int $perPage = 15)
    {
        return ApprovalRequest::where('status', ApprovalStatus::Pending)
            ->whereHas('records', fn($q) => $q->where('approver_id', $userId)->whereNull('acted_at'))
            ->with(['flow', 'user'])
            ->paginate($perPage);
    }
}
