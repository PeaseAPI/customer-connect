<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExpenseStatus;
use App\Models\Expense;
use App\Services\Finance\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends BaseApiController
{
    public function __construct(protected ExpenseService $expenseService) {}

    public function index(Request $request)
    {
        $expenses = $this->expenseService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($expenses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:191',
            'amount' => 'required|numeric',
            'purchase_date' => 'required|date',
            'purchase_from' => 'nullable|string|max:191',
            'category_id' => 'nullable|exists:expense_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'billable' => 'nullable|boolean',
            'note' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');
        $validated['user_id'] = $request->user()->id;
        $validated['status'] = ExpenseStatus::Pending;

        $expense = $this->expenseService->create($validated);

        return $this->success($expense->load(['user', 'project', 'category', 'currency']), '费用创建成功', 201);
    }

    public function show(Expense $expense)
    {
        return $this->success($expense->load(['user', 'project', 'category', 'currency', 'creator']));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'item_name' => 'sometimes|string|max:191',
            'amount' => 'sometimes|numeric',
            'purchase_date' => 'sometimes|date',
            'purchase_from' => 'nullable|string|max:191',
            'category_id' => 'nullable|exists:expense_categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'billable' => 'nullable|boolean',
            'note' => 'nullable|string',
        ]);

        $expense = $this->expenseService->update($expense, $validated);

        return $this->success($expense->load(['user', 'project', 'category', 'currency']), '更新成功');
    }

    public function destroy(Expense $expense)
    {
        $this->expenseService->delete($expense);
        return $this->success(null, '删除成功');
    }

    public function approve(Expense $expense)
    {
        $this->expenseService->approve($expense);
        return $this->success(null, '审批通过');
    }

    public function reject(Request $request, Expense $expense)
    {
        $this->expenseService->reject($expense);
        return $this->success(null, '已拒绝');
    }
}
