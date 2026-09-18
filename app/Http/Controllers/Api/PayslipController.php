<?php

namespace App\Http\Controllers\Api;

use App\Models\Payslip;
use App\Services\Salary\SalaryService;
use Illuminate\Http\Request;

class PayslipController extends BaseApiController
{
    public function __construct(protected SalaryService $salaryService) {}

    public function index(Request $request)
    {
        $payslips = $this->salaryService->listPayslips($request->all(), $request->per_page ?? 15);
        return $this->paginated($payslips);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|date_format:Y-m',
        ]);

        $payslip = $this->salaryService->generatePayslip($validated['user_id'], $validated['month']);
        return $this->success($payslip->load('user'), 'Payslip generated successfully', 201);
    }

    public function batchGenerate(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $result = $this->salaryService->batchGeneratePayslips(
            $validated['month'],
            $request->attributes->get('company_id')
        );

        return $this->success($result, "Generated {$result['generated']} payslips");
    }

    public function send(Payslip $payslip)
    {
        $payslip = $this->salaryService->sendPayslip($payslip);
        return $this->success($payslip, 'Payslip sent');
    }

    public function show(Payslip $payslip)
    {
        return $this->success($payslip->load('user'));
    }
}
