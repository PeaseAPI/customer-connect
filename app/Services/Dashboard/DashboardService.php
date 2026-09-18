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
use App\Enums\UserStatus;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\InvoiceStatus;
use App\Enums\ContractStatus;
use Illuminate\Support\Facades\DB;

class DashboardService
{
        public function getOverview(int $companyId): array
    {
        return [
            'projects_count' => Project::where('company_id', $companyId)->count(),
            'tasks_count' => Task::where('company_id', $companyId)->count(),
            'pending_tasks' => Task::where('company_id', $companyId)
                ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count(),
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
            'active' => (clone $query)->where('status', UserStatus::Active)->count(),
        ];
    }

        public function getProjectStats(int $companyId): array
    {
        $query = Project::where('company_id', $companyId);
        return [
            'total' => $query->count(),
            'in_progress' => (clone $query)->where('status', ProjectStatus::InProgress)->count(),
            'completed' => (clone $query)->where('status', ProjectStatus::Completed)->count(),
            'overdue' => (clone $query)->where('deadline', '<', now())
                ->whereNotIn('status', [ProjectStatus::Completed->value, ProjectStatus::Canceled->value])->count(),
        ];
    }

        public function getTaskStats(int $companyId): array
    {
        $query = Task::where('company_id', $companyId);
        $myTasks = (clone $query)->where('assign_to', auth()->id())
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count();

        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', TaskStatus::Pending)->count(),
            'in_progress' => (clone $query)->where('status', TaskStatus::InProgress)->count(),
            'completed' => (clone $query)->where('status', TaskStatus::Completed)->count(),
            'overdue' => (clone $query)->where('due_date', '<', now())
                ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count(),
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
            ->whereIn('status', [InvoiceStatus::Sent->value, InvoiceStatus::Partial->value])
            ->sum('total');
        $overdueInvoices = Invoice::where('company_id', $companyId)
            ->where('due_date', '<', now())
            ->whereIn('status', [InvoiceStatus::Sent->value, InvoiceStatus::Partial->value])
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
            'active' => (clone $query)->where('status', ContractStatus::Active)->count(),
            'expiring_soon' => (clone $query)->where('status', ContractStatus::Active)
                ->where('end_date', '<=', now()->addDays(30))
                ->where('end_date', '>=', now())->count(),
            'total_value' => (clone $query)->where('status', ContractStatus::Active)->sum('value'),
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

    /**
     * 管理员仪表盘 — 快捷入口+数据卡片+待办+图表
     */
    public function getAdminDashboard(int $companyId, int $userId): array
    {
        $overview = $this->getOverview($companyId);
        $finance = $this->getFinanceStats($companyId);

        $pendingApprovals = \App\Models\ApprovalRequest::where('company_id', $companyId)->where('status', 'pending')->count();
        $expiringContracts = Contract::where('company_id', $companyId)->where('status', ContractStatus::Active)->where('end_date', '<=', now()->addDays(30))->count();
        $overdueInvoices = Invoice::where('company_id', $companyId)->where('due_date', '<', now())->whereIn('status', [InvoiceStatus::Sent->value, InvoiceStatus::Partial->value])->count();
        $pendingTickets = Ticket::where('company_id', $companyId)->where('status', 'open')->count();
        $overdueTasks = Task::where('company_id', $companyId)->where('due_date', '<', now())->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count();

        return array_merge($overview, $finance, [
            'quick_entries' => [
                ['label' => '创建项目', 'route' => 'projects.store'],
                ['label' => '添加任务', 'route' => 'tasks.store'],
                ['label' => '新建发票', 'route' => 'invoices.store'],
                ['label' => '添加客户', 'route' => 'clients.store'],
            ],
            'todo' => [
                ['type' => 'approval', 'label' => 'Pending approval', 'count' => $pendingApprovals],
                ['type' => 'contract', 'label' => '即将到期合同', 'count' => $expiringContracts],
                ['type' => 'invoice', 'label' => '逾期发票', 'count' => $overdueInvoices],
                ['type' => 'ticket', 'label' => '待处理工单', 'count' => $pendingTickets],
                ['type' => 'task', 'label' => '逾期任务', 'count' => $overdueTasks],
            ],
            'charts' => [
                'income_vs_expense' => $this->getIncomeVsExpenseChart($companyId),
                'lead_funnel' => $this->getLeadStats($companyId),
            ],
        ]);
    }

    /**
     * 员工仪表盘 — 我的项目/任务/工时/考勤
     */
    public function getEmployeeDashboard(int $companyId, int $userId): array
    {
        $myTasks = Task::where('company_id', $companyId)->where('assign_to', $userId)
            ->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])
            ->with(['project:id,project_name'])->orderBy('due_date')->limit(10)->get();

        $myProjects = Project::where('company_id', $companyId)
            ->whereHas('members', fn($q) => $q->where('user_id', $userId))
            ->limit(5)->get();

        $todayAttendance = Attendance::where('company_id', $companyId)
            ->where('user_id', $userId)->whereDate('date', now()->toDateString())->first();

        $pendingLeaves = Leave::where('company_id', $companyId)
            ->where('user_id', $userId)->where('status', 'pending')->count();

        $overdueTasks = Task::where('company_id', $companyId)->where('assign_to', $userId)
            ->where('due_date', '<', now())->whereNotIn('status', [TaskStatus::Completed->value, TaskStatus::Cancelled->value])->count();

        $thisWeekHours = \App\Models\Timelog::where('company_id', $companyId)->where('user_id', $userId)
            ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_minutes');

        return [
            'my_tasks' => $myTasks,
            'my_projects' => $myProjects,
            'today_attendance' => $todayAttendance,
            'pending_leaves' => $pendingLeaves,
            'overdue_tasks' => $overdueTasks,
            'this_week_hours' => round($thisWeekHours / 60, 1),
            'quick_actions' => [
                ['label' => '打卡', 'route' => 'attendance.clock-in'],
                ['label' => '申请请假', 'route' => 'leaves.store'],
                ['label' => '提交工时', 'route' => 'timelogs.store'],
            ],
        ];
    }

    /**
     * 客户仪表盘 — 项目/发票/合同/工单
     */
    public function getClientDashboard(int $companyId, int $userId): array
    {
        $myProjects = Project::where('company_id', $companyId)->where('client_id', $userId)->with(['members'])->limit(10)->get();
        $myInvoices = Invoice::where('company_id', $companyId)->where('client_id', $userId)
            ->whereIn('status', [InvoiceStatus::Sent->value, InvoiceStatus::Partial->value, InvoiceStatus::Unpaid->value])
            ->orderBy('due_date')->limit(5)->get();
        $myContracts = Contract::where('company_id', $companyId)->where('client_id', $userId)->where('status', ContractStatus::Active)->get();
        $myTickets = Ticket::where('company_id', $companyId)->where('user_id', $userId)->where('status', 'open')->count();

        return [
            'my_projects' => $myProjects,
            'my_invoices' => $myInvoices,
            'my_contracts' => $myContracts,
            'open_tickets' => $myTickets,
        ];
    }

    private function getIncomeVsExpenseChart(int $companyId): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('Y-m'),
                'income' => Payment::where('company_id', $companyId)->whereYear('paid_on', $date->year)->whereMonth('paid_on', $date->month)->sum('amount'),
                'expense' => Expense::where('company_id', $companyId)->whereYear('purchase_date', $date->year)->whereMonth('purchase_date', $date->month)->sum('amount'),
            ];
        }
        return $months;
    }
}
