<?php

namespace App\Http\Controllers\Api;

use App\Models\ContractSignature;
use App\Services\Contract\ContractSignatureService;
use Illuminate\Http\Request;

class ContractSignatureController extends BaseApiController
{
    public function __construct(protected ContractSignatureService $contractSignatureService) {}

    public function index(Request $request, $contractId)
    {
        $signatures = $this->contractSignatureService->list($contractId, $request->per_page ?? 15);

        return $this->paginated($signatures);
    }

    public function store(Request $request, $contractId)
    {
        $validated = $request->validate([
            'signed_by' => 'required|exists:users,id',
            'signature' => 'required|string',
            'signed_date' => 'required|date',
        ]);

        $validated['contract_id'] = $contractId;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->contractSignatureService->create($validated)->load(['signer']),
            'Signature created successfully',
            201
        );
    }

    public function show($contractId, ContractSignature $signature)
    {
        return $this->success($signature->load(['signer']));
    }

    public function destroy($contractId, ContractSignature $signature)
    {
        $this->contractSignatureService->delete($signature);

        return $this->success(null, 'Deleted successfully');
    }
}
