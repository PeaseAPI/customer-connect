<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreRecurringInvoiceRequest;
use App\Http\Requests\UpdateRecurringInvoiceRequest;
use App\Models\RecurringInvoice;
use App\Services\Finance\RecurringInvoiceService;
use Illuminate\Http\Request;

class RecurringInvoiceController extends BaseApiController
{
    public function __construct(protected RecurringInvoiceService $recurringInvoiceService) {}

    public function index(Request $request)
    {
        $invoices = $this->recurringInvoiceService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($invoices);
    }

    public function store(StoreRecurringInvoiceRequest $request)
    {
        $v = $request->validated();
        $v['added_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');

        $invoice = $this->recurringInvoiceService->create($v);

        return $this->success($invoice->load(['client', 'project', 'currency', 'creator']), '循环发票创建成功', 201);
    }

    public function show(RecurringInvoice $recurringInvoice)
    {
        return $this->success($recurringInvoice->load(['client', 'project', 'currency', 'creator', 'items', 'logs']));
    }

    public function update(UpdateRecurringInvoiceRequest $request, RecurringInvoice $recurringInvoice)
    {
        $v = $request->validated();
        $v['last_updated_by'] = $request->user()->id;

        $recurringInvoice = $this->recurringInvoiceService->update($recurringInvoice, $v);

        return $this->success($recurringInvoice->load(['client', 'project', 'currency', 'creator']), 'Updated successfully');
    }

    public function destroy(RecurringInvoice $recurringInvoice)
    {
        $this->recurringInvoiceService->delete($recurringInvoice);
        return $this->success(null, 'Deleted successfully');
    }
}
