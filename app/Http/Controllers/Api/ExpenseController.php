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

    public function approve(Request $request, Expense $expense)
    {
        if ($expense->status !== ExpenseStatus::Pending) {
            return $this->error('只能审批待审批的费用', 400);
        }

        $validated = $request->validate([
            'remark' => 'nullable|string|max:500',
        ]);

        $expense = $this->expenseService->approve($expense, $request->user()->id, $validated['remark'] ?? null);
        return $this->success($expense->load(['user', 'project', 'category', 'currency', 'approver']), '审批通过');
    }

    public function reject(Request $request, Expense $expense)
    {
        if ($expense->status !== ExpenseStatus::Pending) {
            return $this->error('只能拒绝待审批的费用', 400);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $expense = $this->expenseService->reject($expense, $request->user()->id, $validated['reason']);
        return $this->success($expense->load(['user', 'project', 'category', 'currency', 'approver']), '已拒绝');
    }

    /**
     * 批量审批
     */
    public function batchApprove(Request $request)
    {
        $validated = $request->validate([
            'expense_ids' => 'required|array',
            'expense_ids.*' => 'required|exists:expenses,id',
            'remark' => 'nullable|string|max:500',
        ]);

        $approved = 0;
        $skipped = 0;

        foreach ($validated['expense_ids'] as $expenseId) {
            $expense = Expense::find($expenseId);
            if ($expense && $expense->status === ExpenseStatus::Pending) {
                $this->expenseService->approve($expense, $request->user()->id, $validated['remark'] ?? null);
                $approved++;
            } else {
                $skipped++;
            }
        }

        return $this->success([
            'approved' => $approved,
            'skipped' => $skipped,
        ], "已审批 {$approved} 项，跳过 {$skipped} 项");
    }
}
