<?php

namespace App\Http\Controllers\Api;

use App\Enums\CreditNoteStatus;
use App\Models\CreditNote;
use App\Services\Finance\CreditNoteService;
use Illuminate\Http\Request;

class CreditNoteController extends BaseApiController
{
    public function __construct(protected CreditNoteService $creditNoteService) {}

    public function index(Request $request)
    {
        $notes = $this->creditNoteService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($notes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:users,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'project_id' => 'nullable|exists:projects,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'issue_date' => 'required|date',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'note' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required_with:items|string',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.unit_price' => 'nullable|numeric',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $validated['cn_number'] = 'CN-' . str_pad(CreditNote::max('id') + 1, 6, '0', STR_PAD_LEFT);
                $validated['status'] = CreditNoteStatus::Open->value;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        $creditNote = $this->creditNoteService->create($validated, $items);

        return $this->success($creditNote->load(['client', 'currency', 'items']), '信用票据创建成功', 201);
    }

    public function show(CreditNote $creditNote)
    {
        return $this->success($creditNote->load(['client', 'invoice', 'currency', 'items']));
    }

    public function update(Request $request, CreditNote $creditNote)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:open,closed,draft',
            'note' => 'nullable|string',
        ]);

        $creditNote = $this->creditNoteService->update($creditNote, $validated);

        return $this->success($creditNote->load(['client', 'currency', 'items']), 'Updated successfully');
    }

    public function destroy(CreditNote $creditNote)
    {
        $this->creditNoteService->delete($creditNote);
        return $this->success(null, 'Deleted successfully');
    }
}
