<?php

namespace App\Services\Report;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Task;
use App\Models\Project;
use App\Models\Lead;
use App\Models\Attendance;
use App\Models\Timelog;
use App\Models\Leave;
use App\Enums\TaskStatus;
use App\Enums\ExpenseStatus;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
        public function financeReport(int $companyId, array $filters = []): array
    {
        $year = $filters['year'] ?? now()->year;
        $month = $filters['month'] ?? null;

        $query = fn ($model) => $model::where('company_id', $companyId)
            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
            ->whereYear('created_at', $year);

        $revenue = (clone $query)(Payment::class)->sum('amount');
        $expenses = (clone $query)(Expense::class)->sum('amount');
        $invoiced = (clone $query)(Invoice::class)->sum('total');

        // 月度收入趋势
        $monthlyRevenue = Payment::where('company_id', $companyId)
            ->whereYear('paid_on', $year)
            ->selectRaw('MONTH(paid_on) as month, SUM(amount) as total')
            ->groupByRaw('MONTH(paid_on)')
            ->pluck('total', 'month')
            ->toArray();

        // 月度支出趋势
        $monthlyExpenses = Expense::where('company_id', $companyId)
            ->whereYear('purchase_date', $year)
            ->selectRaw('MONTH(purchase_date) as month, SUM(amount) as total')
            ->groupByRaw('MONTH(purchase_date)')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'net_income' => $revenue - $expenses,
            'invoiced' => $invoiced,
            'monthly_revenue' => $monthlyRevenue,
            'monthly_expenses' => $monthlyExpenses,
                        'expense_by_category' => Expense::where('company_id', $companyId)
                ->whereYear('purchase_date', $year)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->with('category:id,category_name')
                ->get(),
        ];
    }

        public function salesReport(int $companyId, array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfYear()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        $leads = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $total = $leads->count();
        $converted = (clone $leads)->where('is_client', true)->count();

        return [
            'total_leads' => $total,
            'converted_leads' => $converted,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
                        'leads_by_source' => (clone $leads)->selectRaw('source_id, count(*) as count')
                ->groupBy('source_id')->with('source:id,source_name')->get(),
            'leads_by_status' => (clone $leads)->selectRaw('status_id, count(*) as count')
                ->groupBy('status_id')->pluck('count', 'status_id')->toArray(),
            'new_clients' => \App\Models\User::where('company_id', $companyId)
                ->whereHas('roles', fn($q) => $q->where('name', 'client'))
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
        ];
    }

    public function taskReport(int $companyId, array $filters = []): array
    {
        $projectId = $filters['project_id'] ?? null;
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();

        $tasks = Task::where('company_id', $companyId)
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total' => $tasks->count(),
            'by_status' => (clone $tasks)->selectRaw('status, count(*) as count')
                ->groupBy('status')->pluck('count', 'status')->toArray(),
            'by_priority' => (clone $tasks)->selectRaw('priority, count(*) as count')
                ->groupBy('priority')->pluck('count', 'priority')->toArray(),
                        'overdue' => (clone $tasks)->where('due_date', '<', now())
                ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count(),
            'completed_on_time' => (clone $tasks)->where('status', TaskStatus::Completed)
                ->whereColumn('completed_at', '<=', 'due_date')->count(),
        ];
    }

        public function attendanceReport(int $companyId, string $month): array
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('
                user_id,
                COUNT(*) as total_days,
                SUM(CASE WHEN late = "yes" THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN half_day = "yes" THEN 1 ELSE 0 END) as half_day_count
            ')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get();

        return [
            'period' => $month,
            'work_days' => $this->getWorkDays($startDate, $endDate),
            'summary' => $attendances,
        ];
    }

        /**
     * 工时报表 — 项目/员工工时汇总、计费vs非计费
     */
    public function timelogReport(int $companyId, array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? now()->toDateString();
        $projectId = $filters['project_id'] ?? null;
        $userId = $filters['user_id'] ?? null;

        $timelogs = Timelog::where('company_id', $companyId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->when($projectId, fn($q) => $q->where('project_id', $projectId))
            ->when($userId, fn($q) => $q->where('user_id', $userId));

        // 按项目汇总
        $byProject = (clone $timelogs)
            ->selectRaw('project_id, SUM(total_minutes) as total_minutes, COUNT(*) as entry_count')
            ->groupBy('project_id')
            ->with('project:id,project_name')
            ->get();

        // 按员工汇总
        $byUser = (clone $timelogs)
            ->selectRaw('user_id, SUM(total_minutes) as total_minutes, COUNT(*) as entry_count')
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get();

        // 计费 vs 非计费
        $billable = (clone $timelogs)->where('billable', true)->sum('total_minutes');
        $nonBillable = (clone $timelogs)->where('billable', false)->sum('total_minutes');

        return [
            'total_hours' => round((clone $timelogs)->sum('total_minutes') / 60, 1),
            'total_entries' => (clone $timelogs)->count(),
            'billable_hours' => round($billable / 60, 1),
            'non_billable_hours' => round($nonBillable / 60, 1),
            'billable_ratio' => ($billable + $nonBillable) > 0
                ? round($billable / ($billable + $nonBillable) * 100, 1) : 0,
            'by_project' => $byProject,
            'by_user' => $byUser,
        ];
    }

    /**
     * 费用报表 — 分类统计、部门对比、月度趋势
     */
    public function expenseReport(int $companyId, array $filters = []): array
    {
        $year = $filters['year'] ?? now()->year;
        $month = $filters['month'] ?? null;

        $expenses = Expense::where('company_id', $companyId)
            ->whereYear('purchase_date', $year)
            ->when($month, fn($q) => $q->whereMonth('purchase_date', $month));

        $byCategory = (clone $expenses)
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id')
            ->with('category:id,category_name')
            ->get();

        $byStatus = (clone $expenses)
            ->selectRaw('status, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $byProject = (clone $expenses)
            ->selectRaw('project_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('project_id')
            ->with('project:id,project_name')
            ->get();

        $monthlyTrend = (clone $expenses)
            ->selectRaw('MONTH(purchase_date) as month, SUM(amount) as total')
            ->groupByRaw('MONTH(purchase_date)')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'total' => (clone $expenses)->sum('amount'),
            'count' => (clone $expenses)->count(),
            'pending_total' => (clone $expenses)->where('status', ExpenseStatus::Pending)->sum('amount'),
            'approved_total' => (clone $expenses)->where('status', ExpenseStatus::Approved)->sum('amount'),
            'by_category' => $byCategory,
            'by_status' => $byStatus,
            'by_project' => $byProject,
            'monthly_trend' => $monthlyTrend,
        ];
    }

    /**
     * 休假报表 — 部门统计、类型分布、余额汇总
     */
    public function leaveReport(int $companyId, array $filters = []): array
    {
        $year = $filters['year'] ?? now()->year;
        $month = $filters['month'] ?? null;

        $leaves = Leave::where('company_id', $companyId)
            ->whereYear('leave_date', $year)
            ->when($month, fn($q) => $q->whereMonth('leave_date', $month));

        $byType = (clone $leaves)
            ->selectRaw('leave_type_id, COUNT(*) as count, SUM(duration) as total_days')
            ->groupBy('leave_type_id')
            ->with('leaveType:id,name')
            ->get();

        $byStatus = (clone $leaves)
            ->selectRaw('status, COUNT(*) as count, SUM(duration) as total_days')
            ->groupBy('status')
            ->get();

        $monthlyTrend = (clone $leaves)
            ->selectRaw('MONTH(leave_date) as month, COUNT(*) as count, SUM(duration) as total_days')
            ->groupByRaw('MONTH(leave_date)')
            ->pluck('total_days', 'month')
            ->toArray();

        // 员工假期余额汇总
        $balanceSummary = \App\Models\EmployeeLeaveQuota::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->selectRaw('user_id, leave_type_id, SUM(quota) as total_quota, SUM(used) as total_used')
            ->groupBy('user_id', 'leave_type_id')
            ->with(['user:id,name', 'leaveType:id,name'])
            ->get()
            ->groupBy('user_id')
            ->map(fn($items) => [
                'user' => $items->first()->user?->name,
                'quotas' => $items->map(fn($q) => [
                    'type' => $q->leaveType?->name,
                    'quota' => $q->total_quota,
                    'used' => $q->total_used,
                    'remaining' => $q->total_quota - $q->total_used,
                ])->toArray(),
            ])
            ->values()
            ->toArray();

        return [
            'total_leaves' => (clone $leaves)->count(),
            'total_days' => (clone $leaves)->sum('duration'),
            'by_type' => $byType,
            'by_status' => $byStatus,
            'monthly_trend' => $monthlyTrend,
            'balance_summary' => $balanceSummary,
        ];
    }

    private function getWorkDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $current = $start->copy();
        while ($current <= $end) {
            if ($current->isWeekday()) $days++;
            $current->addDay();
        }
        return $days;
    }
}
