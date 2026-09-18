<?php

namespace App\Http\Controllers\Api;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $user = $request->user();

        // 根据角色返回不同的仪表盘视图
        if ($user->isClient()) {
            $data = $this->dashboardService->getClientDashboard($companyId, $user->id);
        } elseif ($user->isAdmin()) {
            $data = $this->dashboardService->getAdminDashboard($companyId, $user->id);
        } else {
            $data = $this->dashboardService->getEmployeeDashboard($companyId, $user->id);
        }

        return $this->success($data);
    }

    public function chartData(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->dashboardService->getFinanceStats($companyId);
        return $this->success($data);
    }
}
