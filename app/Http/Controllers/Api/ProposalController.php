<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Services\Finance\ProposalService;
use Illuminate\Http\Request;

class ProposalController extends BaseApiController
{
    public function __construct(protected ProposalService $proposalService) {}

    public function index(Request $request)
    {
        $proposals = $this->proposalService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($proposals);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'deal_id' => 'nullable|exists:deals,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'subject' => 'nullable|string|max:191',
            'valid_till' => 'nullable|date',
            'note' => 'nullable|string',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required_with:items|string',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.unit_price' => 'nullable|numeric',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $validated['proposal_number'] = 'PROP-' . str_pad(Proposal::max('id') + 1, 6, '0', STR_PAD_LEFT);
        $validated['hash'] = \Illuminate\Support\Str::uuid()->toString();
        $validated['status'] = ProposalStatus::Draft->value;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        $proposal = $this->proposalService->create($validated, $items);

        return $this->success($proposal->load(['client', 'currency', 'items']), 'Proposal created successfully', 201);
    }

    public function show(Proposal $proposal)
    {
        return $this->success($proposal->load(['client', 'currency', 'lead', 'deal', 'items']));
    }

    public function update(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:191',
            'status' => 'sometimes|in:draft,sent,accepted,declined,expired',
            'note' => 'nullable|string',
        ]);

        $status = $validated['status'] ?? null;
        unset($validated['status']);

        if (!empty($validated)) {
            $proposal = $this->proposalService->update($proposal, $validated);
        }

        if ($status) {
            $proposal = $this->proposalService->changeStatus($proposal, $status);
        }

        return $this->success($proposal->load(['client', 'currency', 'items']), 'Updated successfully');
    }

    public function destroy(Proposal $proposal)
    {
        $this->proposalService->delete($proposal);
        return $this->success(null, 'Deleted successfully');
    }

    public function send(Proposal $proposal)
    {
        $proposal = $this->proposalService->send($proposal);
        return $this->success($proposal, 'Proposal sent');
    }

    public function convertToInvoice(Proposal $proposal)
    {
                if ($proposal->status !== ProposalStatus::Accepted) {
            return $this->error('Only accepted proposals can be converted to invoices', 400);
        }

        $invoice = $this->proposalService->convertToInvoice($proposal);
        return $this->success($invoice->load('items'), 'Proposal converted to invoice', 201);
    }
}
