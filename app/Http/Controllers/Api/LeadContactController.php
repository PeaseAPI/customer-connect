<?php

namespace App\Http\Controllers\Api;

use App\Models\LeadContact;
use App\Services\CRM\LeadContactService;
use Illuminate\Http\Request;

class LeadContactController extends BaseApiController
{
    public function __construct(protected LeadContactService $leadContactService) {}

    public function index(Request $request)
    {
        $contacts = $this->leadContactService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($contacts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'contact_name' => 'required|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:30',
            'is_primary' => 'nullable|boolean',
        ]);

        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->leadContactService->create($validated)->load(['lead', 'creator']),
            '联系人创建成功',
            201
        );
    }

    public function show(LeadContact $contact)
    {
        return $this->success($contact->load(['lead', 'creator']));
    }

    public function update(Request $request, LeadContact $contact)
    {
        $validated = $request->validate([
            'contact_name' => 'sometimes|string|max:191',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:30',
            'is_primary' => 'nullable|boolean',
        ]);

        $contact = $this->leadContactService->update($contact, $validated);

        return $this->success($contact->load(['lead', 'creator']), 'Updated successfully');
    }

    public function destroy(LeadContact $contact)
    {
        $this->leadContactService->delete($contact);

        return $this->success(null, 'Deleted successfully');
    }
}
