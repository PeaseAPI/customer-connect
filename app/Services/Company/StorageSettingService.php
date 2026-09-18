<?php

namespace App\Services\Company;

use App\Models\StorageSetting;
use App\Traits\HasCompanyScope;
use Illuminate\Support\Facades\Context;

class StorageSettingService
{
    public function get(int $companyId): ?StorageSetting
    {
        return StorageSetting::where('company_id', $companyId)->first();
    }

    public function update(int $companyId, array $data): StorageSetting
    {
        $setting = StorageSetting::where('company_id', $companyId)->first();

        if (!$setting) {
            $data['company_id'] = $companyId;
            return StorageSetting::create($data);
        }

        $setting->update($data);
        return $setting->fresh();
    }

    /**
     * 获取存储驱动配置（供文件上传使用）
     */
    public function getDriverConfig(int $companyId): array
    {
        $setting = $this->get($companyId);

        if (!$setting) {
            return ['driver' => 'local'];
        }

        return $setting->getDriverConfig();
    }

    /**
     * 测试存储连接
     */
    public function testConnection(int $companyId): array
    {
        $setting = $this->get($companyId);

        if (!$setting) {
            return ['success' => true, 'driver' => 'local', 'message' => '本地存储正常运行'];
        }

        try {
            $disk = \Illuminate\Support\Facades\Storage::disk($setting->default_driver);
            $testFile = 'test_' . time() . '.txt';
            $disk->put($testFile, 'KHT Storage Test');
            $exists = $disk->exists($testFile);
            $disk->delete($testFile);

            return [
                'success' => $exists,
                'driver' => $setting->default_driver,
                'message' => $exists ? '存储连接正常' : '存储写入失败',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'driver' => $setting->default_driver,
                'message' => '连接失败: ' . $e->getMessage(),
            ];
        }
    }
}
