<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreExpenseRecurringRequest;
use App\Http\Requests\UpdateExpenseRecurringRequest;
use App\Models\ExpenseRecurring;
use App\Services\Finance\ExpenseRecurringService;
use Illuminate\Http\Request;

class ExpenseRecurringController extends BaseApiController
{
    public function __construct(protected ExpenseRecurringService $expenseRecurringService) {}

    public function index(Request $request)
    {
        $recurrings = $this->expenseRecurringService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($recurrings);
    }

    public function store(StoreExpenseRecurringRequest $request)
    {
        $v = $request->validated();
        $v['company_id'] = $request->attributes->get('company_id');
        return $this->success($this->expenseRecurringService->create($v)->load(['expense']), '循环费用创建成功', 201);
    }

    public function show(ExpenseRecurring $expenseRecurring)
    {
        return $this->success($expenseRecurring->load(['expense']));
    }

    public function update(UpdateExpenseRecurringRequest $request, ExpenseRecurring $expenseRecurring)
    {
        $v = $request->validated();
        $expenseRecurring = $this->expenseRecurringService->update($expenseRecurring, $v);
        return $this->success($expenseRecurring->load(['expense']), '更新成功');
    }

    public function destroy(ExpenseRecurring $expenseRecurring)
    {
        $this->expenseRecurringService->delete($expenseRecurring);
        return $this->success(null, '删除成功');
    }
}
