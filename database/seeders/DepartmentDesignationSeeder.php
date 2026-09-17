<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Designation;
use Illuminate\Database\Seeder;

class DepartmentDesignationSeeder extends Seeder
{
    public function run(): void
    {
        // 部门 - 公司1
        $departments = [
            ['company_id' => 1, 'department_name' => '技术部'],
            ['company_id' => 1, 'department_name' => '销售部'],
            ['company_id' => 1, 'department_name' => '人事部'],
            ['company_id' => 1, 'department_name' => '财务部'],
            ['company_id' => 1, 'department_name' => '市场部'],
            ['company_id' => 1, 'department_name' => '运营部'],
            ['company_id' => 1, 'department_name' => '客服部'],
            ['company_id' => 1, 'department_name' => '管理层'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // 职位 - 公司1
        $designations = [
            ['company_id' => 1, 'designation_name' => 'CEO'],
            ['company_id' => 1, 'designation_name' => 'CTO'],
            ['company_id' => 1, 'designation_name' => '部门经理'],
            ['company_id' => 1, 'designation_name' => '高级工程师'],
            ['company_id' => 1, 'designation_name' => '工程师'],
            ['company_id' => 1, 'designation_name' => '初级工程师'],
            ['company_id' => 1, 'designation_name' => '销售代表'],
            ['company_id' => 1, 'designation_name' => '设计师'],
            ['company_id' => 1, 'designation_name' => '产品经理'],
            ['company_id' => 1, 'designation_name' => '测试工程师'],
            ['company_id' => 1, 'designation_name' => '运营专员'],
            ['company_id' => 1, 'designation_name' => '客服专员'],
        ];

        foreach ($designations as $des) {
            Designation::create($des);
        }

        $this->command->info('已创建 ' . count($departments) . ' 个部门和 ' . count($designations) . ' 个职位');
    }
}
