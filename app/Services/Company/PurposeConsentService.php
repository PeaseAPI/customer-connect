<?php

namespace App\Services\Company;

use App\Enums\ApprovalStatus;
use App\Models\PurposeConsent;
use App\Models\PurposeConsentUser;
use App\Models\PurposeConsentLead;
use App\Models\RemovalRequest;
use App\Models\RemovalRequestLead;

class PurposeConsentService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = PurposeConsent::with(['consentUsers', 'consentLeads']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): PurposeConsent
    {
        return PurposeConsent::create($data);
    }

    public function update(PurposeConsent $purposeConsent, array $data): PurposeConsent
    {
        $purposeConsent->update($data);
        return $purposeConsent->fresh();
    }

    public function delete(PurposeConsent $purposeConsent): bool
    {
        return $purposeConsent->delete();
    }

    public function consentUser(int $consentId, array $data, string $ip, string $userAgent, int $companyId): PurposeConsentUser
    {
        $data['purpose_consent_id'] = $consentId;
        $data['ip_address'] = $ip;
        $data['user_agent'] = $userAgent;
        $data['consented_at'] = now();
        $data['company_id'] = $companyId;

        return PurposeConsentUser::create($data);
    }

    public function consentLead(int $consentId, array $data, string $ip, string $userAgent, int $companyId): PurposeConsentLead
    {
        $data['purpose_consent_id'] = $consentId;
        $data['ip_address'] = $ip;
        $data['user_agent'] = $userAgent;
        $data['consented_at'] = now();
        $data['company_id'] = $companyId;

        return PurposeConsentLead::create($data);
    }

    public function removalRequests(array $filters = [], int $perPage = 15)
    {
        $query = RemovalRequest::with(['user', 'approver']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function storeRemovalRequest(array $data): RemovalRequest
    {
        return RemovalRequest::create($data);
    }

        public function approveRemovalRequest(RemovalRequest $removalRequest, int $approvedBy): RemovalRequest
    {
        $removalRequest->update([
            'status' => ApprovalStatus::Approved,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
        return $removalRequest->fresh()->load(['user', 'approver']);
    }

    public function rejectRemovalRequest(RemovalRequest $removalRequest, int $approvedBy): RemovalRequest
    {
        $removalRequest->update([
            'status' => ApprovalStatus::Rejected,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
        return $removalRequest->fresh()->load(['user', 'approver']);
    }

    public function leadRemovalRequests(array $filters = [], int $perPage = 15)
    {
        $query = RemovalRequestLead::with(['lead', 'approver']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function storeLeadRemovalRequest(array $data): RemovalRequestLead
    {
        return RemovalRequestLead::create($data);
    }
}

