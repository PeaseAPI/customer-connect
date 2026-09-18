<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\GdprSetting;
use App\Models\InvoiceSetting;
use App\Models\LeaveType;
use App\Models\OfflinePaymentMethod;
use App\Models\Project;
use App\Models\Shift;
use App\Models\Tax;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModuleDataSeeder extends Seeder
{
    /**
     * Seed module default data for each company.
     * Run after CompanySeeder so companies exist.
     */
    public function run(): void
    {
        $this->command->info('🔧 Seeding module data...');

        $companies = Company::all();

        foreach ($companies as $company) {
            // Shifts
            $shifts = collect([
                ['shift_name' => '早班', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'half_day_mark_time' => '12:00:00', 'late_mark_after' => 15],
                ['shift_name' => '正常班', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'half_day_mark_time' => '13:00:00', 'late_mark_after' => 15],
                ['shift_name' => '晚班', 'start_time' => '16:00:00', 'end_time' => '00:00:00', 'half_day_mark_time' => '20:00:00', 'late_mark_after' => 15],
                ['shift_name' => '夜班', 'start_time' => '00:00:00', 'end_time' => '08:00:00', 'half_day_mark_time' => '04:00:00', 'late_mark_after' => 15],
            ])->map(fn($s) => Shift::firstOrCreate(
                ['company_id' => $company->id, 'shift_name' => $s['shift_name']],
                $s,
            ));

            // Leave Types
            $leaveTypes = collect([
                ['type_name' => '年假', 'is_paid' => true, 'paid_leaves' => 15, 'carry_forward' => true],
                ['type_name' => '病假', 'is_paid' => true, 'paid_leaves' => 10, 'carry_forward' => false],
                ['type_name' => '事假', 'is_paid' => false, 'paid_leaves' => 0, 'carry_forward' => false],
                ['type_name' => '婚假', 'is_paid' => true, 'paid_leaves' => 3, 'carry_forward' => false],
                ['type_name' => '产假', 'is_paid' => true, 'paid_leaves' => 98, 'carry_forward' => false],
                ['type_name' => '丧假', 'is_paid' => true, 'paid_leaves' => 3, 'carry_forward' => false],
            ])->map(fn($lt) => LeaveType::firstOrCreate(
                ['company_id' => $company->id, 'type_name' => $lt['type_name']],
                $lt,
            ));

            // Taxes
            Tax::firstOrCreate(
                ['company_id' => $company->id, 'tax_name' => '增值税'],
                ['tax_percent' => 13.00, 'is_active' => true, 'include_in_total' => true],
            );
            Tax::firstOrCreate(
                ['company_id' => $company->id, 'tax_name' => '企业所得税'],
                ['tax_percent' => 25.00, 'is_active' => false, 'include_in_total' => false],
            );

            // Unit Types
            $units = ['个', '件', '套', '箱', '吨', '千克', '米', '小时', '天', '月'];
            foreach ($units as $unit) {
                UnitType::firstOrCreate(
                    ['company_id' => $company->id, 'unit_type' => $unit],
                );
            }

                        // Offline Payment Methods
            OfflinePaymentMethod::firstOrCreate(
                ['company_id' => $company->id, 'method_name' => '银行转账'],
                [
                    'description' => '请通过银行转账完成付款',
                    'bank_name' => '中国工商银行',
                    'bank_account_number' => '6222 0000 0000 0000',
                    'bank_code' => 'ICBKCNBJ',
                    'is_active' => true,
                ],
            );
            OfflinePaymentMethod::firstOrCreate(
                ['company_id' => $company->id, 'method_name' => '支付宝'],
                ['description' => '请通过支付宝完成付款', 'is_active' => true],
            );

            // GDPR Settings
            GdprSetting::firstOrCreate(
                ['company_id' => $company->id],
                [
                    'gdpr_enable' => false,
                    'show_consent_on_signup' => false,
                    'allow_right_to_be_forgotten' => false,
                    'allow_data_export' => false,
                ],
            );

            // Invoice Settings
            InvoiceSetting::firstOrCreate(
                ['company_id' => $company->id],
            );

            // Projects (for milestone/timelog testing)
            $companyUsers = User::where('company_id', $company->id)->get();
            $clientUser = $companyUsers->first(fn($u) => $u->hasRole('client'));

            $projectNames = ['KHT官网改版', 'ERP系统开发', '移动端APP', '数据分析平台'];
            foreach ($projectNames as $idx => $name) {
                Project::firstOrCreate(
                    ['company_id' => $company->id, 'project_name' => $name],
                    [
                        'client_id' => $clientUser?->id,
                        'start_date' => now()->subMonths(3 - $idx),
                        'deadline' => now()->addMonths(6 + $idx),
                        'status' => ProjectStatus::InProgress->value,
                        'budget' => rand(50000, 500000),
                        'created_by' => $companyUsers->first()?->id,
                    ],
                );
            }

            // Expense Categories
            $expCats = ['办公用品', '差旅费', '软件许可', '市场推广', '员工福利'];
            foreach ($expCats as $cat) {
                ExpenseCategory::firstOrCreate(
                    ['company_id' => $company->id, 'category_name' => $cat],
                );
            }
        }

        $this->command->info('✅ Module data seeding complete!');
    }
}
