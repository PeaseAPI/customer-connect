<?php

namespace App\Http\Controllers\Api;

use App\Models\EmployeeVisa;
use App\Services\HRM\EmployeeVisaService;
use Illuminate\Http\Request;

class EmployeeVisaController extends BaseApiController
{
    public function __construct(protected EmployeeVisaService $visaService) {}

    public function index(Request $request, $employeeId)
    {
        $visas = $this->visaService->list($employeeId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($visas);
    }

    public function store(Request $request, $employeeId)
    {
        $validated = $request->validate([
            'visa_type' => 'required|string|max:100',
            'visa_number' => 'nullable|string|max:100',
            'issuing_authority' => 'nullable|string|max:255',
            'issuing_country' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'filename' => 'nullable|string',
            'hashname' => 'nullable|string',
        ]);

        $validated['user_id'] = $employeeId;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->visaService->create($validated)->load(['creator']),
            '签证记录创建成功',
            201
        );
    }

    public function show($employeeId, EmployeeVisa $visa)
    {
        return $this->success($visa->load(['creator']));
    }

    public function update(Request $request, $employeeId, EmployeeVisa $visa)
    {
        $validated = $request->validate([
            'visa_type' => 'sometimes|string|max:100',
            'visa_number' => 'nullable|string|max:100',
            'issuing_authority' => 'nullable|string|max:255',
            'issuing_country' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'filename' => 'nullable|string',
            'hashname' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $visa = $this->visaService->update($visa, $validated);

        return $this->success($visa->load(['creator']), 'Updated successfully');
    }

    public function destroy($employeeId, EmployeeVisa $visa)
    {
        $this->visaService->delete($visa);

        return $this->success(null, 'Deleted successfully');
    }
}
