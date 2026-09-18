<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class DepartmentDesignationSeeder extends Seeder
{
    public function run(): void
    {
        // Department - 公司1
        $departments = [
            ['company_id' => 1, 'department_name' => 'Engineering'],
            ['company_id' => 1, 'department_name' => 'Sales'],
            ['company_id' => 1, 'department_name' => 'Human Resources'],
            ['company_id' => 1, 'department_name' => 'Finance'],
            ['company_id' => 1, 'department_name' => 'Marketing'],
            ['company_id' => 1, 'department_name' => 'Operations'],
            ['company_id' => 1, 'department_name' => 'Customer Support'],
            ['company_id' => 1, 'department_name' => 'Management'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Position - 公司1
        $designations = [
            ['company_id' => 1, 'designation_name' => 'CEO'],
            ['company_id' => 1, 'designation_name' => 'CTO'],
            ['company_id' => 1, 'designation_name' => 'Department Manager'],
            ['company_id' => 1, 'designation_name' => 'Senior Engineer'],
            ['company_id' => 1, 'designation_name' => 'Engineer'],
            ['company_id' => 1, 'designation_name' => 'Junior Engineer'],
            ['company_id' => 1, 'designation_name' => 'Sales Representative'],
            ['company_id' => 1, 'designation_name' => 'Designer'],
            ['company_id' => 1, 'designation_name' => 'Product Manager'],
            ['company_id' => 1, 'designation_name' => 'QA Engineer'],
            ['company_id' => 1, 'designation_name' => 'Operations Specialist'],
            ['company_id' => 1, 'designation_name' => 'Support Specialist'],
        ];

        foreach ($designations as $des) {
            Designation::create($des);
        }

        $this->command->info('Created ' . count($departments) . ' departments and ' . count($designations) . ' designations');
    }
}
