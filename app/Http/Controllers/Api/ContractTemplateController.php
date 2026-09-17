<?php

namespace App\Http\Controllers\Api;

use App\Models\ContractTemplate;
use App\Services\Contract\ContractTemplateService;
use Illuminate\Http\Request;

class ContractTemplateController extends BaseApiController
{
    public function __construct(protected ContractTemplateService $contractTemplateService) {}

    public function index(Request $request)
    {
        $templates = $this->contractTemplateService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($templates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'contract_detail' => 'nullable|string',
            'contract_type_id' => 'nullable|exists:contract_types,id',
            'description' => 'nullable|string',
        ]);

        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->contractTemplateService->create($validated)->load(['contractType', 'creator']),
            '合同模板创建成功',
            201
        );
    }

    public function show(ContractTemplate $contractTemplate)
    {
        return $this->success($contractTemplate->load(['contractType', 'creator']));
    }

    public function update(Request $request, ContractTemplate $contractTemplate)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'contract_detail' => 'nullable|string',
            'contract_type_id' => 'nullable|exists:contract_types,id',
            'description' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $contractTemplate = $this->contractTemplateService->update($contractTemplate, $validated);

        return $this->success($contractTemplate->load(['contractType', 'creator']), '更新成功');
    }

    public function destroy(ContractTemplate $contractTemplate)
    {
        $this->contractTemplateService->delete($contractTemplate);

        return $this->success(null, '删除成功');
    }
}
