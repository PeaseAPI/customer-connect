<?php

namespace App\Http\Controllers\Api;

use App\Models\ContractRenewHistory;
use App\Services\Contract\ContractRenewHistoryService;
use Illuminate\Http\Request;

class ContractRenewHistoryController extends BaseApiController
{
    public function __construct(protected ContractRenewHistoryService $contractRenewHistoryService) {}

    public function index(Request $request, $contractId)
    {
        $history = $this->contractRenewHistoryService->list($contractId, $request->per_page ?? 15);

        return $this->paginated($history);
    }

    public function store(Request $request, $contractId)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);

        $validated['contract_id'] = $contractId;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->contractRenewHistoryService->create($validated)->load(['creator']),
            'Renewal record created successfully',
            201
        );
    }

    public function show($contractId, ContractRenewHistory $renewHistory)
    {
        return $this->success($renewHistory->load(['creator']));
    }

    public function update(Request $request, $contractId, ContractRenewHistory $renewHistory)
    {
        $validated = $request->validate([
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $renewHistory = $this->contractRenewHistoryService->update($renewHistory, $validated);

        return $this->success($renewHistory->load(['creator']), 'Updated successfully');
    }

    public function destroy($contractId, ContractRenewHistory $renewHistory)
    {
        $this->contractRenewHistoryService->delete($renewHistory);

        return $this->success(null, 'Deleted successfully');
    }
}
