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
        $data = $this->dashboardService->getOverview($companyId);
        return $this->success($data);
    }

    public function chartData(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->dashboardService->getFinanceStats($companyId);
        return $this->success($data);
    }
}
