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
                // Super Admin - Company 1
        $superAdmin = User::create([
            'company_id' => 1, 'name' => 'System Admin', 'email' => 'admin@example.com',
            'mobile' => '13800138000', 'password' => Hash::make('123456'),
            'gender' => Gender::Other, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $superAdmin->assignRole('super-admin');
        UserAuth::create(['user_id' => $superAdmin->id, 'company_id' => 1, 'is_superadmin' => true]);

        // Admin - Company 1
        $admin = User::create([
            'company_id' => 1, 'name' => 'John Manager', 'email' => 'manager@example.com',
            'mobile' => '13800138001', 'password' => Hash::make('123456'),
            'gender' => Gender::Male, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $admin->assignRole('admin');
        UserAuth::create(['user_id' => $admin->id, 'company_id' => 1]);

        // HR Manager
        $hr = User::create([
            'company_id' => 1, 'name' => 'Jane HR', 'email' => 'hr@example.com',
            'mobile' => '13800138002', 'password' => Hash::make('123456'),
            'gender' => Gender::Female, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $hr->assignRole('hr-manager');
        UserAuth::create(['user_id' => $hr->id, 'company_id' => 1]);

        // Sales Manager
        $sales = User::create([
            'company_id' => 1, 'name' => 'Bob Sales', 'email' => 'sales@example.com',
            'mobile' => '13800138003', 'password' => Hash::make('123456'),
            'gender' => Gender::Male, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $sales->assignRole('sales-manager');
        UserAuth::create(['user_id' => $sales->id, 'company_id' => 1]);

        // Project Manager
        $pm = User::create([
            'company_id' => 1, 'name' => 'Alice PM', 'email' => 'pm@example.com',
            'mobile' => '13800138004', 'password' => Hash::make('123456'),
            'gender' => Gender::Male, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $pm->assignRole('project-manager');
        UserAuth::create(['user_id' => $pm->id, 'company_id' => 1]);

        // Finance Manager
        $finance = User::create([
            'company_id' => 1, 'name' => 'Carol Finance', 'email' => 'finance@example.com',
            'mobile' => '13800138005', 'password' => Hash::make('123456'),
            'gender' => Gender::Female, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $finance->assignRole('finance-manager');
        UserAuth::create(['user_id' => $finance->id, 'company_id' => 1]);

        $this->seedEmployees();
        $this->seedClients();
        $this->seedDemoCompany();

        $this->command->info('Created ' . User::count() . ' users');
    }

    private function seedEmployees(): void
    {
        $employees = [
            ['name' => 'Dave Developer', 'email' => 'dev1@example.com', 'mobile' => '13800138010', 'gender' => Gender::Male],
            ['name' => 'Eve Tester', 'email' => 'dev2@example.com', 'mobile' => '13800138011', 'gender' => Gender::Female],
            ['name' => 'Frank Designer', 'email' => 'dev3@example.com', 'mobile' => '13800138012', 'gender' => Gender::Female],
            ['name' => 'Grace Ops', 'email' => 'dev4@example.com', 'mobile' => '13800138013', 'gender' => Gender::Male],
            ['name' => 'Helen Marketing', 'email' => 'dev5@example.com', 'mobile' => '13800138014', 'gender' => Gender::Female],
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
            ['name' => 'Client Alpha', 'email' => 'client1@example.com', 'mobile' => '13900139001'],
            ['name' => 'Client Beta', 'email' => 'client2@example.com', 'mobile' => '13900139002'],
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
            'company_id' => 2, 'name' => 'Demo Admin', 'email' => 'demo@example.com',
            'mobile' => '13800138006', 'password' => Hash::make('123456'),
            'gender' => Gender::Other, 'status' => UserStatus::Active,
            'login' => 'enable', 'email_notifications' => true, 'country_id' => 1,
        ]);
        $demoAdmin->assignRole('admin');
        UserAuth::create(['user_id' => $demoAdmin->id, 'company_id' => 2, 'is_superadmin' => true]);
    }
}
