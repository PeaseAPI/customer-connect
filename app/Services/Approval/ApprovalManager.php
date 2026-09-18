<?php

namespace App\Services\Approval;

use Illuminate\Support\Facades\Log;

class ApprovalManager
{
    public function driver(?string $provider = null): ApprovalServiceInterface
    {
        $provider = $provider ?? config('services.approval.driver', 'internal');
        return match ($provider) {
            'dingtalk' => new DingtalkApprovalService(),
            'internal' => new InternalApprovalService(),
            default => throw new \InvalidArgumentException("Unsupported approval service: {$provider}"),
        };
    }

    public function createInstance(?string $provider, array $data): string
    {
        try {
            return $this->driver($provider)->createInstance($data);
        } catch (\Exception $e) {
            Log::error('Failed to create approval instance', ['provider' => $provider, 'error' => $e->getMessage()]);
            return '';
        }
    }
}
