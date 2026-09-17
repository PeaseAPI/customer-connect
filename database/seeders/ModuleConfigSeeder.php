<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\LeadSource;
use App\Models\LeadStage;
use App\Models\LeaveType;
use App\Models\ContractType;
use Illuminate\Database\Seeder;

class ModuleConfigSeeder extends Seeder
{
    public function run(): void
    {
        // 线索阶段
        $stages = [
            ['company_id' => 1, 'stage_name' => '新线索', 'priority' => 1, 'is_default' => true],
            ['company_id' => 1, 'stage_name' => '已联系', 'priority' => 2],
            ['company_id' => 1, 'stage_name' => '需求确认', 'priority' => 3],
            ['company_id' => 1, 'stage_name' => '方案报价', 'priority' => 4],
            ['company_id' => 1, 'stage_name' => '商务谈判', 'priority' => 5],
            ['company_id' => 1, 'stage_name' => '已成交', 'priority' => 6],
            ['company_id' => 1, 'stage_name' => '已流失', 'priority' => 7],
        ];
        foreach ($stages as $stage) {
            LeadStage::create($stage);
        }

        // 线索来源
        $sources = [
            ['company_id' => 1, 'source_name' => '官网'],
            ['company_id' => 1, 'source_name' => '微信公众号'],
            ['company_id' => 1, 'source_name' => '抖音'],
            ['company_id' => 1, 'source_name' => '老客户推荐'],
            ['company_id' => 1, 'source_name' => '展会'],
            ['company_id' => 1, 'source_name' => '电话咨询'],
            ['company_id' => 1, 'source_name' => '其他'],
        ];
        foreach ($sources as $source) {
            LeadSource::create($source);
        }

        // 假期类型
        $leaveTypes = [
            ['company_id' => 1, 'type_name' => '年假', 'is_paid' => true, 'paid_leaves' => 10, 'carry_forward' => true],
            ['company_id' => 1, 'type_name' => '事假', 'is_paid' => false, 'paid_leaves' => 0, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => '病假', 'is_paid' => true, 'paid_leaves' => 15, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => '婚假', 'is_paid' => true, 'paid_leaves' => 10, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => '产假', 'is_paid' => true, 'paid_leaves' => 158, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => '陪产假', 'is_paid' => true, 'paid_leaves' => 15, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => '丧假', 'is_paid' => true, 'paid_leaves' => 3, 'carry_forward' => false],
        ];
        foreach ($leaveTypes as $lt) {
            LeaveType::create($lt);
        }

        // 费用类别
        $expenseCategories = [
            ['company_id' => 1, 'category_name' => '办公费用'],
            ['company_id' => 1, 'category_name' => '差旅费用'],
            ['company_id' => 1, 'category_name' => '招待费用'],
            ['company_id' => 1, 'category_name' => '培训费用'],
            ['company_id' => 1, 'category_name' => '交通费用'],
            ['company_id' => 1, 'category_name' => '通讯费用'],
            ['company_id' => 1, 'category_name' => '市场推广'],
            ['company_id' => 1, 'category_name' => '软件订阅'],
        ];
        foreach ($expenseCategories as $ec) {
            ExpenseCategory::create($ec);
        }

        // 合同类型
        $contractTypes = [
            ['company_id' => 1, 'name' => '固定期限合同'],
            ['company_id' => 1, 'name' => '无固定期限合同'],
            ['company_id' => 1, 'name' => '实习协议'],
            ['company_id' => 1, 'name' => '劳务合同'],
            ['company_id' => 1, 'name' => '服务合同'],
            ['company_id' => 1, 'name' => '保密协议'],
        ];
        foreach ($contractTypes as $ct) {
            ContractType::create($ct);
        }

        $this->command->info('已创建模块配置数据：线索阶段、来源、假期类型、费用类别、合同类型');
    }
}
