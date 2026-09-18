<?php

namespace App\Services\Company;

use App\Models\CustomModule;
use App\Models\CustomModuleData;
use Illuminate\Support\Str;

class CustomModuleService
{
    /**
     * 列出自定义模块
     */
    public function list(int $companyId, int $perPage = 15)
    {
        return CustomModule::where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * 创建自定义模块
     */
    public function create(int $companyId, array $data): CustomModule
    {
        $data['company_id'] = $companyId;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return CustomModule::create($data);
    }

    /**
     * 更新自定义模块
     */
    public function update(CustomModule $module, array $data): CustomModule
    {
        $module->update($data);
        return $module->fresh();
    }

    /**
     * 删除自定义模块（及其所有数据）
     */
    public function delete(CustomModule $module): void
    {
        $module->records()->delete();
        $module->delete();
    }

    /**
     * 添加模块数据记录
     */
    public function addRecord(CustomModule $module, array $data, int $userId): CustomModuleData
    {
        return CustomModuleData::create([
            'company_id' => $module->company_id,
            'custom_module_id' => $module->id,
            'data' => $data,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    /**
     * 更新模块数据记录
     */
    public function updateRecord(CustomModuleData $record, array $data, int $userId): CustomModuleData
    {
        $record->update([
            'data' => $data,
            'updated_by' => $userId,
        ]);
        return $record->fresh();
    }

    /**
     * 删除模块数据记录
     */
    public function deleteRecord(CustomModuleData $record): void
    {
        $record->delete();
    }

    /**
     * 获取模块数据列表
     */
    public function listRecords(CustomModule $module, int $perPage = 15)
    {
        return $module->records()
            ->with(['creator', 'updater'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
