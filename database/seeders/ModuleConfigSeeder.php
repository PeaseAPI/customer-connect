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
            ['company_id' => 1, 'stage_name' => 'New lead', 'priority' => 1, 'is_default' => true],
            ['company_id' => 1, 'stage_name' => 'Contacted', 'priority' => 2],
            ['company_id' => 1, 'stage_name' => 'Requirements confirmed', 'priority' => 3],
            ['company_id' => 1, 'stage_name' => 'Proposal quote', 'priority' => 4],
            ['company_id' => 1, 'stage_name' => 'Business negotiation', 'priority' => 5],
            ['company_id' => 1, 'stage_name' => 'Closed', 'priority' => 6],
            ['company_id' => 1, 'stage_name' => 'Lost', 'priority' => 7],
        ];
        foreach ($stages as $stage) {
            LeadStage::create($stage);
        }

        // 线索Source
        $sources = [
            ['company_id' => 1, 'source_name' => 'Website'],
            ['company_id' => 1, 'source_name' => 'WeChat Official Account'],
            ['company_id' => 1, 'source_name' => 'TikTok'],
            ['company_id' => 1, 'source_name' => 'Referral'],
            ['company_id' => 1, 'source_name' => 'Exhibition'],
            ['company_id' => 1, 'source_name' => 'Phone inquiry'],
            ['company_id' => 1, 'source_name' => 'Other'],
        ];
        foreach ($sources as $source) {
            LeadSource::create($source);
        }

        // 假期类型
        $leaveTypes = [
            ['company_id' => 1, 'type_name' => 'Annual leave', 'is_paid' => true, 'paid_leaves' => 10, 'carry_forward' => true],
            ['company_id' => 1, 'type_name' => 'Personal leave', 'is_paid' => false, 'paid_leaves' => 0, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => 'Sick leave', 'is_paid' => true, 'paid_leaves' => 15, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => 'Marriage leave', 'is_paid' => true, 'paid_leaves' => 10, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => 'Maternity leave', 'is_paid' => true, 'paid_leaves' => 158, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => 'Paternity leave', 'is_paid' => true, 'paid_leaves' => 15, 'carry_forward' => false],
            ['company_id' => 1, 'type_name' => 'Bereavement leave', 'is_paid' => true, 'paid_leaves' => 3, 'carry_forward' => false],
        ];
        foreach ($leaveTypes as $lt) {
            LeaveType::create($lt);
        }

        // Expense Category
        $expenseCategories = [
            ['company_id' => 1, 'category_name' => 'Office expense'],
            ['company_id' => 1, 'category_name' => 'Travel expense'],
            ['company_id' => 1, 'category_name' => 'Entertainment expense'],
            ['company_id' => 1, 'category_name' => 'Training expense'],
            ['company_id' => 1, 'category_name' => 'Transportation'],
            ['company_id' => 1, 'category_name' => 'Communication expense'],
            ['company_id' => 1, 'category_name' => 'Marketing promotion'],
            ['company_id' => 1, 'category_name' => 'Software subscription'],
        ];
        foreach ($expenseCategories as $ec) {
            ExpenseCategory::create($ec);
        }

        // 合同类型
        $contractTypes = [
            ['company_id' => 1, 'name' => 'Fixed-term contract'],
            ['company_id' => 1, 'name' => 'Open-ended contract'],
            ['company_id' => 1, 'name' => 'Internship agreement'],
            ['company_id' => 1, 'name' => 'Labor contract'],
            ['company_id' => 1, 'name' => 'Service contract'],
            ['company_id' => 1, 'name' => 'Non-disclosure agreement'],
        ];
        foreach ($contractTypes as $ct) {
            ContractType::create($ct);
        }

        $this->command->info('Created module config data: lead stages, sources, leave types, expense categories, contract types');
    }
}
