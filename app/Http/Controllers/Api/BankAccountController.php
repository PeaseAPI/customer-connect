<?php

namespace App\Http\Controllers\Api;

use App\Models\BankAccount;
use App\Services\Finance\BankAccountService;
use Illuminate\Http\Request;

class BankAccountController extends BaseApiController
{
    public function __construct(protected BankAccountService $bankAccountService) {}

    public function index(Request $request)
    {
        $accounts = $this->bankAccountService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($accounts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:191',
            'branch_name' => 'nullable|string|max:191',
            'account_name' => 'required|string|max:191',
            'account_number' => 'required|string|max:50',
            'account_type' => 'nullable|string|max:30',
            'sort_code' => 'nullable|string|max:30',
            'currency_id' => 'nullable|exists:currencies,id',
            'contact_number' => 'nullable|string|max:30',
            'opening_balance' => 'nullable|numeric',
        ]);
                $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');
        $account = $this->bankAccountService->create($validated);
        return $this->success($account->load('currency'), 'Bank account created successfully', 201);
    }

    public function show(BankAccount $bankAccount)
    {
        return $this->success($bankAccount->load('currency'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'bank_name' => 'sometimes|string|max:191',
            'branch_name' => 'nullable|string|max:191',
            'account_name' => 'sometimes|string|max:191',
            'account_number' => 'sometimes|string|max:50',
            'status' => 'nullable|in:active,inactive',
        ]);
        $bankAccount = $this->bankAccountService->update($bankAccount, $validated);
        return $this->success($bankAccount->load('currency'), 'Updated successfully');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $this->bankAccountService->delete($bankAccount);
        return $this->success(null, 'Deleted successfully');
    }
}
