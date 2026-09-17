<?php

namespace App\Services\Company;

use App\Models\GdprSetting;

class GdprSettingService
{
    public function get(int $companyId): GdprSetting
    {
        return GdprSetting::firstOrCreate(
            ['company_id' => $companyId],
            ['gdpr_enable' => false]
        );
    }

    public function update(int $companyId, array $data): GdprSetting
    {
        return GdprSetting::updateOrCreate(
            ['company_id' => $companyId],
            $data
        );
    }
}

