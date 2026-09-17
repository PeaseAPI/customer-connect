<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 开始填充数据库...');

        // 1. 基础数据（无依赖）
        $this->call([
            PackageSeeder::class,
            CountryCurrencySeeder::class,
            RolePermissionSeeder::class,
        ]);

        // 2. 公司和组织设置
        $this->call([
            CompanySeeder::class,
            OrganisationSettingSeeder::class,
        ]);

        // 3. 组织架构
        $this->call([
            DepartmentDesignationSeeder::class,
        ]);

        // 4. 用户和权限
        $this->call([
            UserSeeder::class,
        ]);

        // 5. 模块配置
        $this->call([
            ModuleConfigSeeder::class,
        ]);

        // 6. Worksuite 子模块数据
        $this->call([
            WorksuiteSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ 数据库填充完成！');
        $this->command->info('');
        $this->command->info('📋 测试账号：');
        $this->command->info('  超级管理员: admin@kht.com / 123456');
        $this->command->info('  管理员:     manager@kht.com / 123456');
        $this->command->info('  HR经理:     hr@kht.com / 123456');
        $this->command->info('  销售经理:   sales@kht.com / 123456');
        $this->command->info('  项目经理:   pm@kht.com / 123456');
        $this->command->info('  财务经理:   finance@kht.com / 123456');
        $this->command->info('  演示账号:   demo@kht.com / 123456');
    }
}

