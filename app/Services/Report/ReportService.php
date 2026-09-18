<?php

namespace App\Services\Report;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Task;
use App\Models\Project;
use App\Models\Lead;
use App\Models\Attendance;
use App\Enums\TaskStatus;
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
