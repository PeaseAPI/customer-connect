<?php

namespace Database\Seeders;

use App\Enums\CompanyStatus;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'company_name' => '客户通科技有限公司',
                'company_email' => 'admin@kht.com',
                'company_phone' => '400-888-9999',
                'subdomain' => 'kht',
                'status' => CompanyStatus::Active,
                'package_id' => 1,
                'license_type' => 'regular',
                'license_expire_on' => now()->addYear(),
            ],
            [
                'company_name' => '演示企业',
                'company_email' => 'demo@kht.com',
                'company_phone' => '400-666-8888',
                'subdomain' => 'demo',
                'status' => CompanyStatus::Active,
                'package_id' => 2,
                'license_type' => 'trial',
                'license_expire_on' => now()->addDays(14),
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }

        $this->command->info('已创建 ' . count($companies) . ' 家公司');
    }
}
