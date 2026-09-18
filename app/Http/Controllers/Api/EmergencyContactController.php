<?php

namespace App\Http\Controllers\Api;

use App\Models\EmergencyContact;
use App\Services\HRM\EmergencyContactService;
use Illuminate\Http\Request;

class EmergencyContactController extends BaseApiController
{
    public function __construct(protected EmergencyContactService $emergencyContactService) {}

    public function index(Request $request, $employeeId)
    {
        $contacts = $this->emergencyContactService->list($employeeId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($contacts);
    }

    public function store(Request $request, $employeeId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:50',
            'relation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
        ]);

        $validated['user_id'] = $employeeId;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->emergencyContactService->create($validated)->load(['creator']),
            'Emergency contact created successfully',
            201
        );
    }

    public function show($employeeId, EmergencyContact $emergencyContact)
    {
        return $this->success($emergencyContact->load(['creator']));
    }

    public function update(Request $request, $employeeId, EmergencyContact $emergencyContact)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'mobile' => 'nullable|string|max:50',
            'relation' => 'nullable|string|max:100',
            'address' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $emergencyContact = $this->emergencyContactService->update($emergencyContact, $validated);

        return $this->success($emergencyContact->load(['creator']), 'Updated successfully');
    }

    public function destroy($employeeId, EmergencyContact $emergencyContact)
    {
        $this->emergencyContactService->delete($emergencyContact);

        return $this->success(null, 'Deleted successfully');
    }
}

