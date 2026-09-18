<?php

namespace Database\Seeders;

use App\Models\OrganisationSetting;
use Illuminate\Database\Seeder;

class OrganisationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'company_id' => 1,
                'company_name' => '客户通科技有限公司',
                'company_email' => 'admin@example.com',
                'company_phone' => '400-888-9999',
                'logo' => null,
                'currency_id' => 1,
                'timezone' => 'Asia/Shanghai',
                'date_format' => 'Y-m-d',
                'time_format' => '24',
                'fiscal_year' => '1-1',
                'leaves_start_from' => 'joining_date',
                'active_theme' => 'default',
                'task_self' => true,
                'lead_source' => 'manual',
                'after_login' => 'dashboard',
            ],
            [
                'company_id' => 2,
                'company_name' => '演示企业',
                'company_email' => 'demo@example.com',
                'company_phone' => '400-666-8888',
                'logo' => null,
                'currency_id' => 1,
                'timezone' => 'Asia/Shanghai',
                'date_format' => 'Y-m-d',
                'time_format' => '24',
                'fiscal_year' => '1-1',
                'leaves_start_from' => 'year_start',
                'active_theme' => 'default',
                'task_self' => false,
                'lead_source' => 'manual',
                'after_login' => 'dashboard',
            ],
        ];

        foreach ($settings as $setting) {
            OrganisationSetting::create($setting);
        }

        $this->command->info('Created ' . count($settings) . ' 条组织设置');
    }
}

