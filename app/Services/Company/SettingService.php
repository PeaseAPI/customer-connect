<?php

namespace App\Services\Company;

use App\Models\OrganisationSetting;
use App\Models\GdprSetting;
use App\Models\NotificationSetting;

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

    public function getGdpr(int $companyId): ?GdprSetting
    {
        return GdprSetting::where('company_id', $companyId)->first();
    }

    public function updateGdpr(int $companyId, array $data): GdprSetting
    {
        return GdprSetting::updateOrCreate(
            ['company_id' => $companyId],
            $data
        );
    }

    public function getNotificationSettings(int $companyId)
    {
        return NotificationSetting::where('company_id', $companyId)->get();
    }

    public function updateNotificationSetting(int $companyId, string $type, array $data): NotificationSetting
    {
        return NotificationSetting::updateOrCreate(
            ['company_id' => $companyId, 'type' => $type],
            $data
        );
    }

    /**
     * 获取所有设置汇总
     */
    public function getAllSettings(int $companyId): array
    {
        return [
            'organisation' => $this->getOrganisation($companyId),
            'gdpr' => $this->getGdpr($companyId),
            'notifications' => $this->getNotificationSettings($companyId),
        ];
    }

    /**
     * 获取模块特定设置
     */
    public function getModuleSetting(int $companyId, string $module): ?OrganisationSetting
    {
        $setting = OrganisationSetting::where('company_id', $companyId)->first();
        if (!$setting) return null;

        $moduleFields = $this->getModuleFields($module);
        return collect($setting->toArray())->only($moduleFields)->toArray();
    }

    public function updateModuleSetting(int $companyId, string $module, array $data): OrganisationSetting
    {
        $setting = OrganisationSetting::where('company_id', $companyId)->first();
        $moduleFields = $this->getModuleFields($module);
        $filteredData = collect($data)->only($moduleFields)->toArray();
        $setting->update($filteredData);
        return $setting->fresh();
    }

    private function getModuleFields(string $module): array
    {
        return match($module) {
            'invoice' => ['invoice_prefix', 'invoice_template', 'invoice_start_number', 'invoice_digits', 'invoice_payment_term'],
            'project' => ['project_prefix', 'project_start_number', 'project_digits'],
            'task' => ['task_prefix', 'task_start_number', 'task_digits'],
            'attendance' => ['attendance_ip_restrict', 'allowed_ips', 'late_mark_after', 'early_clock_out_before'],
            'leave' => ['leave_start_month', 'leaves_allowed_from', 'leave_department_specific'],
            'timelog' => ['timelog_daily_limit', 'timelog_auto_clock_out'],
            'contract' => ['contract_prefix', 'contract_start_number', 'contract_digits'],
            'ticket' => ['ticket_prefix', 'ticket_start_number', 'ticket_digits'],
            'lead' => ['lead_prefix', 'lead_start_number', 'lead_digits'],
            default => [],
        };
    }
}

