<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Services\Finance\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends BaseApiController
{
    public function __construct(protected PaymentService $paymentService) {}

    public function index(Request $request, $invoiceId = null)
    {
        $filters = $request->all();
        if ($invoiceId) {
            $filters['invoice_id'] = $invoiceId;
        }
        $payments = $this->paymentService->list($filters, $request->per_page ?? 15);
        return $this->paginated($payments);
    }

    public function store(Request $request, $invoiceId = null)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'amount' => 'required|numeric',
            'gateway' => 'required|in:alipay,wechat,stripe,paypal,offline',
            'currency_id' => 'nullable|exists:currencies,id',
            'paid_on' => 'required|date',
            'transaction_id' => 'nullable|string|max:191',
            'note' => 'nullable|string',
        ]);

        if ($invoiceId) {
            $validated['invoice_id'] = $invoiceId;
        }

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        $payment = $this->paymentService->create($validated);

        return $this->success($payment->load(['client', 'invoice', 'currency']), '支付记录创建成功', 201);
    }

    public function show(Payment $payment)
    {
        return $this->success($payment->load(['client', 'invoice', 'currency', 'creator']));
    }

    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric',
            'gateway' => 'sometimes|in:alipay,wechat,stripe,paypal,offline',
            'paid_on' => 'sometimes|date',
            'transaction_id' => 'nullable|string|max:191',
            'note' => 'nullable|string',
        ]);

        $payment = $this->paymentService->update($payment, $validated);

        return $this->success($payment->load(['client', 'invoice', 'currency']), 'Updated successfully');
    }

    public function destroy(Payment $payment)
    {
        $this->paymentService->delete($payment);
        return $this->success(null, 'Deleted successfully');
    }
}
