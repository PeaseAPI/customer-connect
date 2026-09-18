<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Task;
use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getOverview(int $companyId): array
    {
        return [
            'projects_count' => Project::where('company_id', $companyId)->count(),
            'tasks_count' => Task::where('company_id', $companyId)->count(),
            'pending_tasks' => Task::where('company_id', $companyId)
                ->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'invoices_count' => Invoice::where('company_id', $companyId)->count(),
            'leads_count' => Lead::where('company_id', $companyId)->count(),
            'tickets_count' => 0,
            'employees_count' => User::where('company_id', $companyId)->count(),
        ];
    }

    public function getLeadStats(int $companyId): array
    {
        $query = Lead::where('company_id', $companyId);
        $total = $query->count();
        $thisMonth = (clone $query)->whereMonth('created_at', now()->month)->count();
        $converted = (clone $query)->where('is_client', true)->count();
        $conversionRate = $total > 0 ? round(($converted / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'this_month' => $thisMonth,
            'converted' => $converted,
            'conversion_rate' => $conversionRate,
            'by_status' => (clone $query)->select('status_id', DB::raw('count(*) as count'))
                ->groupBy('status_id')->pluck('count', 'status_id')->toArray(),
        ];
    }

    public function getClientStats(int $companyId): array
    {
        $query = User::where('company_id', $companyId)
            ->whereHas('roles', fn($q) => $q->where('name', 'client'));
        return [
            'total' => $query->count(),
            'this_month' => (clone $query)->whereMonth('created_at', now()->month)->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
        ];
    }

    public function getProjectStats(int $companyId): array
    {
        $query = Project::where('company_id', $companyId);
        return [
            'total' => $query->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'overdue' => (clone $query)->where('deadline', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])->count(),
        ];
    }

    public function getTaskStats(int $companyId): array
    {
        $query = Task::where('company_id', $companyId);
        $myTasks = (clone $query)->where('assign_to', auth()->id())
            ->whereNotIn('status', ['completed', 'cancelled'])->count();

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'overdue' => (clone $query)->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'my_tasks' => $myTasks,
        ];
    }

        public function getFinanceStats(int $companyId): array
    {
        $totalRevenue = Payment::where('company_id', $companyId)
            ->whereYear('paid_on', now()->year)
            ->sum('amount');
        $totalExpenses = Expense::where('company_id', $companyId)
            ->whereYear('purchase_date', now()->year)
            ->sum('amount');
        $pendingInvoices = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'partial'])
            ->sum('total');
        $overdueInvoices = Invoice::where('company_id', $companyId)
            ->where('due_date', '<', now())
            ->whereIn('status', ['sent', 'partial'])
            ->sum('total');

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $totalRevenue - $totalExpenses,
            'pending_invoices' => $pendingInvoices,
            'overdue_invoices' => $overdueInvoices,
            'monthly_revenue' => $this->getMonthlyRevenue($companyId),
        ];
    }

    public function getContractStats(int $companyId): array
    {
        $query = Contract::where('company_id', $companyId);
        return [
            'total' => $query->count(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'expiring_soon' => (clone $query)->where('status', 'active')
                ->where('end_date', '<=', now()->addDays(30))
                ->where('end_date', '>=', now())->count(),
            'total_value' => (clone $query)->where('status', 'active')->sum('amount'),
        ];
    }

        private function getMonthlyRevenue(int $companyId): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Payment::where('company_id', $companyId)
                ->whereYear('paid_on', $date->year)
                ->whereMonth('paid_on', $date->month)
                ->sum('amount');
            $months[] = [
                'month' => $date->format('Y-m'),
                'revenue' => $revenue,
            ];
        }
        return $months;
    }
}
