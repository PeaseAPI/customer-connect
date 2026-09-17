<?php

namespace App\Services\Company;

use App\Models\OrganisationSetting;

class SettingService
{
    public function getOrganisation(int $companyId): ?OrganisationSetting
    {
        return OrganisationSetting::where('company_id', $companyId)->first();
    }

    public function updateOrganisation(int $companyId, array $data): OrganisationSetting
    {
        $setting = OrganisationSetting::where('company_id', $companyId)->first();
        $setting->update($data);
        return $setting->fresh();
    }
}

