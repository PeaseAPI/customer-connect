<?php

namespace App\Http\Controllers\Api;

use App\Models\StorageSetting;
use App\Services\Company\StorageSettingService;
use Illuminate\Http\Request;

class StorageSettingController extends BaseApiController
{
    public function __construct(protected StorageSettingService $storageSettingService) {}

    /**
     * 获取存储设置
     */
    public function show()
    {
        $companyId = request()->attributes->get('company_id');
        $setting = $this->storageSettingService->get($companyId);
        return $this->success($setting);
    }

    /**
     * 更新存储设置
     */
    public function update(Request $request)
    {
        $companyId = $request->attributes->get('company_id');

        $validated = $request->validate([
            'default_driver' => 'sometimes|in:local,s3,oss,cos',
            'max_file_size' => 'nullable|integer|min:1024',
            'allowed_types' => 'nullable|array',
            // S3
            's3_key' => 'nullable|string|max:255',
            's3_secret' => 'nullable|string|max:255',
            's3_region' => 'nullable|string|max:100',
            's3_bucket' => 'nullable|string|max:255',
            's3_url' => 'nullable|url|max:255',
            // OSS
            'oss_access_key_id' => 'nullable|string|max:255',
            'oss_access_key_secret' => 'nullable|string|max:255',
            'oss_endpoint' => 'nullable|string|max:255',
            'oss_bucket' => 'nullable|string|max:255',
            'oss_url' => 'nullable|url|max:255',
            'oss_is_cname' => 'nullable|boolean',
            // COS
            'cos_app_id' => 'nullable|string|max:255',
            'cos_secret_id' => 'nullable|string|max:255',
            'cos_secret_key' => 'nullable|string|max:255',
            'cos_region' => 'nullable|string|max:100',
            'cos_bucket' => 'nullable|string|max:255',
            'cos_url' => 'nullable|url|max:255',
        ]);

        $setting = $this->storageSettingService->update($companyId, $validated);
        return $this->success($setting, '存储设置更新成功');
    }

    /**
     * 测试存储连接
     */
    public function testConnection()
    {
        $companyId = request()->attributes->get('company_id');
        $result = $this->storageSettingService->testConnection($companyId);
        return $this->success($result);
    }
}
