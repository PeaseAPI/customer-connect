<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 超级管理员 - 公司1
        $superAdmin = User::create([
            'company_id' => 1, 'name' => '系统管理员', 'email' => 'admin@kht.com',
            'mobile' => '13800138000', 'password' => Hash::make('123456'),
            'gender' => Gender::Other, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $superAdmin->assignRole('super-admin');
        UserAuth::create(['user_id' => $superAdmin->id, 'company_id' => 1, 'is_superadmin' => true]);

        // 管理员 - 公司1
        $admin = User::create([
            'company_id' => 1, 'name' => '张经理', 'email' => 'manager@kht.com',
            'mobile' => '13800138001', 'password' => Hash::make('123456'),
            'gender' => Gender::Male, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $admin->assignRole('admin');
        UserAuth::create(['user_id' => $admin->id, 'company_id' => 1]);

        // HR经理
        $hr = User::create([
            'company_id' => 1, 'name' => '李人事', 'email' => 'hr@kht.com',
            'mobile' => '13800138002', 'password' => Hash::make('123456'),
            'gender' => Gender::Female, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $hr->assignRole('hr-manager');
        UserAuth::create(['user_id' => $hr->id, 'company_id' => 1]);

        // 销售经理
        $sales = User::create([
            'company_id' => 1, 'name' => '王销售', 'email' => 'sales@kht.com',
            'mobile' => '13800138003', 'password' => Hash::make('123456'),
            'gender' => Gender::Male, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $sales->assignRole('sales-manager');
        UserAuth::create(['user_id' => $sales->id, 'company_id' => 1]);

        // 项目经理
        $pm = User::create([
            'company_id' => 1, 'name' => '赵项目', 'email' => 'pm@kht.com',
            'mobile' => '13800138004', 'password' => Hash::make('123456'),
            'gender' => Gender::Male, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $pm->assignRole('project-manager');
        UserAuth::create(['user_id' => $pm->id, 'company_id' => 1]);

        // 财务经理
        $finance = User::create([
            'company_id' => 1, 'name' => '钱财务', 'email' => 'finance@kht.com',
            'mobile' => '13800138005', 'password' => Hash::make('123456'),
            'gender' => Gender::Female, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $finance->assignRole('finance-manager');
        UserAuth::create(['user_id' => $finance->id, 'company_id' => 1]);

        $this->seedEmployees();
        $this->seedClients();
        $this->seedDemoCompany();

        $this->command->info('已创建 ' . User::count() . ' 个用户');
    }

    private function seedEmployees(): void
    {
        $employees = [
            ['name' => '孙开发', 'email' => 'dev1@kht.com', 'mobile' => '13800138010', 'gender' => Gender::Male],
            ['name' => '周测试', 'email' => 'dev2@kht.com', 'mobile' => '13800138011', 'gender' => Gender::Female],
            ['name' => '吴设计', 'email' => 'dev3@kht.com', 'mobile' => '13800138012', 'gender' => Gender::Female],
            ['name' => '郑运营', 'email' => 'dev4@kht.com', 'mobile' => '13800138013', 'gender' => Gender::Male],
            ['name' => '冯市场', 'email' => 'dev5@kht.com', 'mobile' => '13800138014', 'gender' => Gender::Female],
        ];

        foreach ($employees as $emp) {
            $user = User::create([
                'company_id' => 1, 'name' => $emp['name'], 'email' => $emp['email'],
                'mobile' => $emp['mobile'], 'password' => Hash::make('123456'),
                'gender' => $emp['gender'], 'status' => UserStatus::Active,
                'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
            ]);
            $user->assignRole('employee');
            UserAuth::create(['user_id' => $user->id, 'company_id' => 1]);
        }
    }

    private function seedClients(): void
    {
        $clients = [
            ['name' => '刘客户A', 'email' => 'client1@kht.com', 'mobile' => '13900139001'],
            ['name' => '陈客户B', 'email' => 'client2@kht.com', 'mobile' => '13900139002'],
        ];

        foreach ($clients as $client) {
            $user = User::create([
                'company_id' => 1, 'name' => $client['name'], 'email' => $client['email'],
                'mobile' => $client['mobile'], 'password' => Hash::make('123456'),
                'gender' => Gender::Other, 'status' => UserStatus::Active,
                'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
            ]);
            $user->assignRole('client');
            UserAuth::create(['user_id' => $user->id, 'company_id' => 1]);
        }
    }

    private function seedDemoCompany(): void
    {
        $demoAdmin = User::create([
            'company_id' => 2, 'name' => '演示管理员', 'email' => 'demo@kht.com',
            'mobile' => '13800138006', 'password' => Hash::make('123456'),
            'gender' => Gender::Other, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $demoAdmin->assignRole('admin');
        UserAuth::create(['user_id' => $demoAdmin->id, 'company_id' => 2, 'is_superadmin' => true]);
    }
}
