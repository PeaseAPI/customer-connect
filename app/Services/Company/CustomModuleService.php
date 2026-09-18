<?php

namespace App\Services\Company;

use App\Models\CustomModule;
use App\Models\CustomModuleData;
use Illuminate\Support\Str;

class CustomModuleService
{
    /**
     * List custom modules
     */
    public function list(int $companyId, int $perPage = 15)
    {
        return CustomModule::where('company_id', $companyId)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Create custom module
     */
    public function create(int $companyId, array $data): CustomModule
    {
        $data['company_id'] = $companyId;
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return CustomModule::create($data);
    }

    /**
     * Update custom module
     */
    public function update(CustomModule $module, array $data): CustomModule
    {
        $module->update($data);
        return $module->fresh();
    }

    /**
     * Delete custom module and all its data
     */
    public function delete(CustomModule $module): void
    {
        $module->records()->delete();
        $module->delete();
    }

    /**
     * Add module data record
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
     * Update module data record
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
     * Delete module data record
     */
    public function deleteRecord(CustomModuleData $record): void
    {
        $record->delete();
    }

    /**
     * List module data
     */
    public function listRecords(CustomModule $module, int $perPage = 15)
    {
        return $module->records()
            ->with(['creator', 'updater'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
