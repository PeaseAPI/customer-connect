<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApprovalStatus;
use App\Http\Requests\StorePurposeConsentRequest;
use App\Http\Requests\UpdatePurposeConsentRequest;
use App\Http\Requests\ConsentUserRequest;
use App\Http\Requests\ConsentLeadRequest;
use App\Http\Requests\StoreRemovalRequestRequest;
use App\Http\Requests\StoreLeadRemovalRequestRequest;
use App\Models\PurposeConsent;
use App\Models\RemovalRequest;
use App\Models\RemovalRequestLead;
use App\Services\Company\PurposeConsentService;
use Illuminate\Http\Request;

class PurposeConsentController extends BaseApiController
{
    public function __construct(protected PurposeConsentService $purposeConsentService) {}

    public function index(Request $request)
    {
        $consents = $this->purposeConsentService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($consents);
    }

        public function store(StorePurposeConsentRequest $request)
    {
        $v = $request->validated();
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->purposeConsentService->create($v)->load(['consentUsers', 'consentLeads']), 'Consent purpose created successfully', 201);
    }

    public function show(PurposeConsent $purposeConsent)
    {
        return $this->success($purposeConsent->load(['consentUsers.user', 'consentLeads.lead']));
    }

    public function update(UpdatePurposeConsentRequest $request, PurposeConsent $purposeConsent)
    {
        $v = $request->validated();
        $purposeConsent = $this->purposeConsentService->update($purposeConsent, $v);
        return $this->success($purposeConsent, 'Updated successfully');
    }

    public function destroy(PurposeConsent $purposeConsent)
    {
        $this->purposeConsentService->delete($purposeConsent);
        return $this->success(null, 'Deleted successfully');
    }

    // Consent for a user
    public function consentUser(ConsentUserRequest $request, $consentId)
    {
        $v = $request->validated();
        return $this->success(
            $this->purposeConsentService->consentUser($consentId, $v, $request->ip(), $request->userAgent(), $request->attributes->get('company_id')),
            'User consent recorded successfully',
            201
        );
    }

    // Consent for a lead
    public function consentLead(ConsentLeadRequest $request, $consentId)
    {
        $v = $request->validated();
        return $this->success(
            $this->purposeConsentService->consentLead($consentId, $v, $request->ip(), $request->userAgent(), $request->attributes->get('company_id')),
            'Lead consent recorded successfully',
            201
        );
    }

    // Removal requests
    public function removalRequests(Request $request)
    {
        $requests = $this->purposeConsentService->removalRequests($request->all(), $request->per_page ?? 15);
        return $this->paginated($requests);
    }

    public function storeRemovalRequest(StoreRemovalRequestRequest $request)
    {
        $v = $request->validated();
        $v['status'] = ApprovalStatus::Pending;
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->purposeConsentService->storeRemovalRequest($v), 'Data deletion request created successfully', 201);
    }

    public function approveRemovalRequest(Request $request, RemovalRequest $removalRequest)
    {
        $removalRequest = $this->purposeConsentService->approveRemovalRequest($removalRequest, $request->user()->id);
        return $this->success($removalRequest, 'Data deletion request approved');
    }

    public function rejectRemovalRequest(Request $request, RemovalRequest $removalRequest)
    {
        $removalRequest = $this->purposeConsentService->rejectRemovalRequest($removalRequest, $request->user()->id);
        return $this->success($removalRequest, 'Data deletion request rejected');
    }

    // Lead removal requests
    public function leadRemovalRequests(Request $request)
    {
        $requests = $this->purposeConsentService->leadRemovalRequests($request->all(), $request->per_page ?? 15);
        return $this->paginated($requests);
    }

    public function storeLeadRemovalRequest(StoreLeadRemovalRequestRequest $request)
    {
        $v = $request->validated();
        $v['status'] = ApprovalStatus::Pending;
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->purposeConsentService->storeLeadRemovalRequest($v), 'Lead data deletion request created successfully', 201);
    }
}
