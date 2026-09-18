<?php

namespace App\Http\Controllers\Api;

use App\Models\ExpenseCategory;
use App\Services\Finance\ExpenseCategoryService;
use Illuminate\Http\Request;

class ExpenseCategoryController extends BaseApiController
{
    public function __construct(protected ExpenseCategoryService $expenseCategoryService) {}

    public function index(Request $request)
    {
        $categories = $this->expenseCategoryService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:191',
            'description' => 'nullable|string',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success($this->expenseCategoryService->create($validated), 'Expense category created', 201);
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        return $this->success($expenseCategory);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validated = $request->validate([
            'category_name' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
        ]);

        $expenseCategory = $this->expenseCategoryService->update($expenseCategory, $validated);

        return $this->success($expenseCategory, 'Updated successfully');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $this->expenseCategoryService->delete($expenseCategory);

        return $this->success(null, 'Deleted successfully');
    }
}
