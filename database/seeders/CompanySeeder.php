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
                'company_name' => 'Customer Connect Technologies Ltd.',
                'company_email' => 'admin@example.com',
                'company_phone' => '400-888-9999',
                'subdomain' => 'demo',
                'status' => CompanyStatus::Active,
                'package_id' => 1,
                'license_type' => 'regular',
                'license_expire_on' => now()->addYear(),
            ],
            [
                'company_name' => 'Demo Company',
                'company_email' => 'demo@example.com',
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

        $this->command->info('Created ' . count($companies) . ' companies');
    }
}
