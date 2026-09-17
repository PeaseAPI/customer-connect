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
                'name' => '免费版',
                'description' => '适合小型团队，基础功能',
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
                'name' => '标准版',
                'description' => '适合成长型企业，全功能模块',
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
                'name' => '专业版',
                'description' => '适合中大型企业，高级功能+定制支持',
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
                'name' => '旗舰版',
                'description' => '不限人数，专属客服+私有部署',
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

        $this->command->info('已创建 ' . count($packages) . ' 个套餐');
    }
}

