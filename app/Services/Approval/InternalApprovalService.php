<?php

namespace App\Services\Approval;

use App\Models\Approval;
use App\Models\ApprovalStep;
use App\Models\ApprovalNode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InternalApprovalService implements ApprovalServiceInterface
{
    public function createInstance(array $data): string
    {
        try {
            DB::beginTransaction();
            $approval = Approval::create([
                'company_id' => $data['company_id'],
                'title' => $data['title'],
                'type' => $data['type'],
                'applicant_id' => $data['applicant_id'],
                'form_data' => $data['form_data'] ?? [],
                'status' => 'pending',
                'flow_config' => $data['flow_config'] ?? [],
            ]);
            $steps = $data['flow_config']['steps'] ?? [];
            foreach ($steps as $index => $step) {
                $approvalStep = ApprovalStep::create([
                    'approval_id' => $approval->id,
                    'company_id' => $data['company_id'],
                    'step_number' => $index + 1,
                    'step_name' => $step['name'] ?? '步骤' . ($index + 1),
                    'approval_type' => $step['type'] ?? 'or',
                    'status' => $index === 0 ? 'active' : 'pending',
                ]);
                foreach ($step['approvers'] ?? [] as $approverId) {
                    ApprovalNode::create([
                        'approval_id' => $approval->id,
                        'step_id' => $approvalStep->id,
                        'company_id' => $data['company_id'],
                        'approver_id' => $approverId,
                        'status' => 'pending',
                    ]);
                }
            }
            DB::commit();
            return (string) $approval->id;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('创建内部审批实例失败', ['error' => $e->getMessage()]);
            return '';
        }
    }

    public function getInstance(string $instanceId): array
    {
        $approval = Approval::with(['steps.nodes', 'applicant'])->find($instanceId);
        return $approval ? $approval->toArray() : [];
    }

    public function registerCallback(string $url): void
    {
        // 内置审批不需要回调注册
    }

    public function testConnection(): bool
    {
        return true;
    }

    public function getConfig(): array
    {
        return ['type' => 'internal', 'name' => '内置审批'];
    }

    public function setConfig(array $config): void
    {
        // 内置审批无需额外配置
    }
}
