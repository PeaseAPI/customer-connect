<?php

namespace App\Http\Controllers\Api;

use App\Services\Report\ReportService;
use Illuminate\Http\Request;

class ReportController extends BaseApiController
{
    public function __construct(protected ReportService $reportService) {}

    public function finance(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->reportService->financeReport($companyId, $request->only(['year', 'month']));
        return $this->success($data);
    }

    public function tasks(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->reportService->taskReport($companyId, $request->only(['project_id', 'start_date', 'end_date']));
        return $this->success($data);
    }

    public function attendance(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $month = $request->input('month', now()->format('Y-m'));
        $data = $this->reportService->attendanceReport($companyId, $month);
        return $this->success($data);
    }

    public function sales(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->reportService->salesReport($companyId, $request->only(['start_date', 'end_date', 'year']));
        return $this->success($data);
    }

    public function expenses(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->reportService->financeReport($companyId, $request->only(['year']));
        return $this->success($data);
    }

    public function leaves(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $data = $this->reportService->attendanceReport($companyId, $request->input('month', now()->format('Y-m')));
        return $this->success($data);
    }
}



