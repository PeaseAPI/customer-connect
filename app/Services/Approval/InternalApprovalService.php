<?php

namespace App\Services\Approval;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalFlow;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InternalApprovalService implements ApprovalServiceInterface
{
    public function createInstance(array $data): string
    {
        try {
            DB::beginTransaction();

            // Find or create an ApprovalFlow for this company + type
            $flow = ApprovalFlow::firstOrCreate(
                [
                    'company_id' => $data['company_id'],
                    'external_type' => $data['type'] ?? 'internal',
                ],
                [
                    'steps' => $data['flow_config']['steps'] ?? [],
                    'is_active' => true,
                    'config' => $data['flow_config'] ?? [],
                ]
            );

            // Create the approval request
            $approvalRequest = ApprovalRequest::create([
                'company_id' => $data['company_id'],
                'flow_id' => $flow->id,
                'user_id' => $data['applicant_id'],
                'status' => ApprovalStatus::Pending,
                'current_step' => 0,
                'external_instance_id' => $data['external_instance_id'] ?? null,
                'form_data' => $data['form_data'] ?? [],
            ]);

            // Create approval records for each approver in each step
            $steps = $data['flow_config']['steps'] ?? [];
            foreach ($steps as $stepIndex => $step) {
                foreach ($step['approvers'] ?? [] as $approverId) {
                    ApprovalRecord::create([
                        'company_id' => $data['company_id'],
                        'request_id' => $approvalRequest->id,
                        'approver_id' => $approverId,
                        'step' => $stepIndex + 1,
                    ]);
                }
            }

            DB::commit();
            return (string) $approvalRequest->id;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create internal approval instance', ['error' => $e->getMessage()]);
            return '';
        }
    }

    public function getInstance(string $instanceId): array
    {
        $approvalRequest = ApprovalRequest::with(['flow', 'user', 'records.approver'])->find($instanceId);
        return $approvalRequest ? $approvalRequest->toArray() : [];
    }

    public function registerCallback(string $url): void
    {
        // Internal approval不需要回调注册
    }

    public function testConnection(): bool
    {
        return true;
    }

    public function getConfig(): array
    {
        return ['type' => 'internal', 'name' => 'Internal approval'];
    }

    public function setConfig(array $config): void
    {
        // Internal approval无需额外配置
    }
}
