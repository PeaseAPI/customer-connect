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
     * 列出自定义模块
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $modules = $this->customModuleService->list($companyId, $request->per_page ?? 15);
        return $this->paginated($modules);
    }

    /**
     * 创建自定义模块
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

        return $this->success($module, '自定义模块创建成功', 201);
    }

    /**
     * 查看自定义模块详情
     */
    public function show(CustomModule $customModule)
    {
        return $this->success($customModule);
    }

    /**
     * 更新自定义模块
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
     * 删除自定义模块
     */
    public function destroy(CustomModule $customModule)
    {
        $this->customModuleService->delete($customModule);
        return $this->success(null, '自定义模块已删除');
    }

    // ===== 模块数据 CRUD =====

    /**
     * 获取模块数据列表
     */
    public function listRecords(Request $request, CustomModule $customModule)
    {
        $records = $this->customModuleService->listRecords($customModule, $request->per_page ?? 15);
        return $this->paginated($records);
    }

    /**
     * 添加模块数据
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

        return $this->success($record->load(['creator', 'updater']), '记录创建成功', 201);
    }

    /**
     * 更新模块数据
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
     * 删除模块数据
     */
    public function destroyRecord(CustomModule $customModule, CustomModuleData $record)
    {
        $this->customModuleService->deleteRecord($record);
        return $this->success(null, '记录已删除');
    }
}
