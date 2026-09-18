<?php

namespace App\Http\Controllers\Api;

use App\Models\SalaryStructure;
use App\Services\Salary\SalaryService;
use Illuminate\Http\Request;

class SalaryStructureController extends BaseApiController
{
    public function __construct(protected SalaryService $salaryService) {}

    public function index(Request $request)
    {
        $structures = $this->salaryService->listStructures($request->all(), $request->per_page ?? 15);
        return $this->paginated($structures);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|array',
            'allowances.*.name' => 'required|string',
            'allowances.*.amount' => 'required|numeric',
            'allowances.*.type' => 'required|in:fixed,percent',
            'deductions' => 'nullable|array',
            'deductions.*.name' => 'required|string',
            'deductions.*.amount' => 'required|numeric',
            'deductions.*.type' => 'required|in:fixed,percent',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        $structure = $this->salaryService->createStructure($validated);
        return $this->success($structure->load('user'), '薪资结构创建成功', 201);
    }

    public function show(SalaryStructure $salaryStructure)
    {
        return $this->success($salaryStructure->load('user'));
    }

    public function update(Request $request, SalaryStructure $salaryStructure)
    {
        $validated = $request->validate([
            'basic_salary' => 'sometimes|numeric|min:0',
            'allowances' => 'nullable|array',
            'deductions' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $structure = $this->salaryService->updateStructure($salaryStructure, $validated);
        return $this->success($structure->load('user'), 'Updated successfully');
    }

    public function destroy(SalaryStructure $salaryStructure)
    {
        $this->salaryService->deleteStructure($salaryStructure);
        return $this->success(null, 'Deleted successfully');
    }
}
