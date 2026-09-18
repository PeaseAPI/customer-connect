<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomModule;
use App\Models\CustomModuleData;
use App\Services\Company\CustomModuleService;
use Illuminate\Http\Request;

class CustomModuleController extends BaseApiController
{
    public function __construct(protected CustomModuleService $customModuleService) {}

    /**
     * List custom modules
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $modules = $this->customModuleService->list($companyId, $request->per_page ?? 15);
        return $this->paginated($modules);
    }

    /**
     * Create custom module
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'icon' => 'nullable|string|max:50',
            'slug' => 'nullable|string|max:100|alpha_dash',
            'menu_position' => 'nullable|in:top,bottom,after_projects,after_crm',
            'sort_order' => 'nullable|integer',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:100',
            'fields.*.type' => 'required|in:text,number,date,select,multi_select,textarea,checkbox,url,email,phone',
            'fields.*.label' => 'required|string|max:191',
            'fields.*.required' => 'nullable|boolean',
            'fields.*.options' => 'nullable|array',
            'list_columns' => 'nullable|array',
            'filters' => 'nullable|array',
        ]);

        $companyId = $request->attributes->get('company_id');
        $module = $this->customModuleService->create($companyId, $validated);

        return $this->success($module, 'Custom module created successfully', 201);
    }

    /**
     * View custom module details
     */
    public function show(CustomModule $customModule)
    {
        return $this->success($customModule);
    }

    /**
     * Update custom module
     */
    public function update(Request $request, CustomModule $customModule)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'icon' => 'nullable|string|max:50',
            'menu_position' => 'nullable|in:top,bottom,after_projects,after_crm',
            'sort_order' => 'nullable|integer',
            'fields' => 'sometimes|array|min:1',
            'list_columns' => 'nullable|array',
            'filters' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $module = $this->customModuleService->update($customModule, $validated);
        return $this->success($module, 'Updated successfully');
    }

    /**
     * Delete custom module
     */
    public function destroy(CustomModule $customModule)
    {
        $this->customModuleService->delete($customModule);
        return $this->success(null, 'Custom module deleted');
    }

    // ===== Module data CRUD =====

    /**
     * List module data
     */
    public function listRecords(Request $request, CustomModule $customModule)
    {
        $records = $this->customModuleService->listRecords($customModule, $request->per_page ?? 15);
        return $this->paginated($records);
    }

    /**
     * Add module data
     */
    public function storeRecord(Request $request, CustomModule $customModule)
    {
        // 动态验证字段
        $rules = [];
        foreach ($customModule->fields as $field) {
            if (!empty($field['required'])) {
                $rules["data.{$field['name']}"] = 'required';
            }
        }

        $validated = $request->validate($rules);

        $record = $this->customModuleService->addRecord(
            $customModule,
            $request->input('data', []),
            $request->user()->id
        );

        return $this->success($record->load(['creator', 'updater']), 'Record created successfully', 201);
    }

    /**
     * Update module data
     */
    public function updateRecord(Request $request, CustomModule $customModule, CustomModuleData $record)
    {
        $record = $this->customModuleService->updateRecord(
            $record,
            $request->input('data', []),
            $request->user()->id
        );

        return $this->success($record->load(['creator', 'updater']), 'Updated successfully');
    }

    /**
     * Delete module data
     */
    public function destroyRecord(CustomModule $customModule, CustomModuleData $record)
    {
        $this->customModuleService->deleteRecord($record);
        return $this->success(null, 'Record deleted');
    }
}
