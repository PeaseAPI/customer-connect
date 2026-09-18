<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Free Edition',
                'description' => 'Suitable for small teams, basic features',
                'is_free' => true,
                'max_employees' => 5,
                'max_storage_mb' => 500,
                'monthly_price' => 0,
                'annual_price' => 0,
                'is_default' => true,
                'is_recommended' => false,
                'sort_order' => 1,
                'status' => 'active',
                'modules' => ['hrm' => true, 'crm' => true, 'pm' => false, 'finance' => false],
            ],
            [
                'name' => 'Standard Edition',
                'description' => 'Suitable for growing businesses, all feature modules',
                'is_free' => false,
                'max_employees' => 50,
                'max_storage_mb' => 5000,
                'monthly_price' => 99,
                'annual_price' => 999,
                'is_default' => false,
                'is_recommended' => true,
                'sort_order' => 2,
                'status' => 'active',
                'modules' => ['hrm' => true, 'crm' => true, 'pm' => true, 'finance' => true],
            ],
            [
                'name' => 'Professional Edition',
                'description' => 'Suitable for mid-to-large enterprises, advanced features + custom support',
                'is_free' => false,
                'max_employees' => 200,
                'max_storage_mb' => 20000,
                'monthly_price' => 299,
                'annual_price' => 2999,
                'is_default' => false,
                'is_recommended' => false,
                'sort_order' => 3,
                'status' => 'active',
                'modules' => ['hrm' => true, 'crm' => true, 'pm' => true, 'finance' => true],
            ],
            [
                'name' => 'Enterprise Edition',
                'description' => 'Unlimited users, dedicated support + private deployment',
                'is_free' => false,
                'max_employees' => 0,
                'max_storage_mb' => 0,
                'monthly_price' => 999,
                'annual_price' => 9999,
                'is_default' => false,
                'is_recommended' => false,
                'sort_order' => 4,
                'status' => 'active',
                'modules' => ['hrm' => true, 'crm' => true, 'pm' => true, 'finance' => true],
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }

        $this->command->info('Created ' . count($packages) . ' packages');
    }
}

