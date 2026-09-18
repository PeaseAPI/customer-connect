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
        $this->command->info('Seeding database...');

        // 1. Base data (no dependencies)
        $this->call([
            PackageSeeder::class,
            CountryCurrencySeeder::class,
            RolePermissionSeeder::class,
        ]);

        // 2. Companies and organization settings
        $this->call([
            CompanySeeder::class,
            OrganisationSettingSeeder::class,
        ]);

        // 3. Organization structure
        $this->call([
            DepartmentDesignationSeeder::class,
        ]);

        // 4. Users and permissions
        $this->call([
            UserSeeder::class,
        ]);

        // 5. Module configuration
        $this->call([
            ModuleConfigSeeder::class,
        ]);

        // 6. Module default data
        $this->call([
            ModuleDataSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('Database seeding complete!');
        $this->command->info('');
        $this->command->info('Test Accounts:');
        $this->command->info('  Super Admin:  admin@example.com / 123456');
        $this->command->info('  Admin:        manager@example.com / 123456');
        $this->command->info('  HR Manager:   hr@example.com / 123456');
        $this->command->info('  Sales Mgr:    sales@example.com / 123456');
        $this->command->info('  Project Mgr:  pm@example.com / 123456');
        $this->command->info('  Finance Mgr:  finance@example.com / 123456');
        $this->command->info('  Demo:         demo@example.com / 123456');
    }
}

