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
     * Get storage driver config (for file uploads)
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
     * Test storage connection
     */
    public function testConnection(int $companyId): array
    {
        $setting = $this->get($companyId);

        if (!$setting) {
            return ['success' => true, 'driver' => 'local', 'message' => 'Local storage running normally'];
        }

        try {
            $disk = \Illuminate\Support\Facades\Storage::disk($setting->default_driver);
            $testFile = 'test_' . time() . '.txt';
                        $disk->put($testFile, 'Customer Connect Storage Test');
            $exists = $disk->exists($testFile);
            $disk->delete($testFile);

            return [
                'success' => $exists,
                'driver' => $setting->default_driver,
                'message' => $exists ? 'Storage connection normal' : 'Storage write failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'driver' => $setting->default_driver,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
