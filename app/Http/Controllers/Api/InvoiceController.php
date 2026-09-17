<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use App\Services\Finance\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends BaseApiController
{
    public function __construct(protected InvoiceService $invoiceService) {}

    public function index(Request $request)
    {
        $invoices = $this->invoiceService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'sub_total' => 'required|numeric',
            'total' => 'required|numeric',
            'date' => 'required|date',
            'due_date' => 'required|date',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'tax' => 'nullable|numeric',
            'currency_id' => 'nullable|exists:currencies,id',
            'note' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required_with:items|string',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.unit_price' => 'nullable|numeric',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        $invoice = $this->invoiceService->create($validated, $items);

        return $this->success($invoice->load(['client', 'project', 'currency', 'items']), '发票创建成功', 201);
    }

    public function show(Invoice $invoice)
    {
        return $this->success($invoice->load(['client', 'project', 'currency', 'items', 'payments', 'creator']));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'note' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'tax' => 'nullable|numeric',
        ]);

        $invoice = $this->invoiceService->update($invoice, $validated);

        return $this->success($invoice->load(['client', 'project', 'currency', 'items']), '更新成功');
    }

    public function destroy(Invoice $invoice)
    {
        $this->invoiceService->delete($invoice);
        return $this->success(null, '删除成功');
    }

    public function send(Invoice $invoice)
    {
        $this->invoiceService->send($invoice);
        return $this->success($invoice->fresh(), '发票已发送');
    }

    public function cancel(Invoice $invoice)
    {
        $this->invoiceService->cancel($invoice);
        return $this->success($invoice->fresh(), '发票已取消');
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'gateway' => 'required|string',
            'transaction_id' => 'nullable|string',
            'paid_on' => 'sometimes|date',
            'note' => 'nullable|string',
        ]);

        $validated['client_id'] = $invoice->client_id;
        $validated['currency_id'] = $invoice->currency_id;
        $validated['created_by'] = $request->user()->id;

        $payment = $this->invoiceService->recordPayment($invoice, $validated);

        return $this->success($payment, '付款记录成功', 201);
    }
}
