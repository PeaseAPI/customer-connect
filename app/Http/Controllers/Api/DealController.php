<?php

namespace App\Http\Controllers\Api;

use App\Models\Deal;
use App\Services\CRM\DealService;
use Illuminate\Http\Request;

class DealController extends BaseApiController
{
    public function __construct(protected DealService $dealService) {}

    public function index(Request $request)
    {
        $deals = $this->dealService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($deals);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'deal_name' => 'required|string|max:191',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'client_id' => 'nullable|exists:users,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'agent_id' => 'nullable|exists:users,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'category_id' => 'nullable|exists:categories,id',
            'value' => 'nullable|numeric',
            'company_name' => 'nullable|string|max:191',
            'website' => 'nullable|string|max:191',
            'client_name' => 'nullable|string|max:191',
            'client_email' => 'nullable|email',
            'mobile' => 'nullable|string|max:30',
            'note' => 'nullable|string',
            'next_follow_up' => 'nullable|date',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        $deal = $this->dealService->create($validated, $request->user()->id);

        return $this->success($deal->load(['stage', 'client', 'agent']), 'Deal created', 201);
    }

    public function show(Deal $deal)
    {
        return $this->success($deal->load(['stage', 'client', 'agent', 'currency', 'source', 'category', 'notes', 'histories']));
    }

    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'deal_name' => 'sometimes|string|max:191',
            'client_id' => 'nullable|exists:users,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'agent_id' => 'nullable|exists:users,id',
            'value' => 'nullable|numeric',
            'note' => 'nullable|string',
            'column_priority' => 'nullable|integer',
        ]);

        $deal = $this->dealService->update($deal, $validated, $request->user()->id);

        return $this->success($deal->load(['stage', 'client', 'agent']), 'Updated successfully');
    }

    public function destroy(Deal $deal)
    {
        $this->dealService->delete($deal);
        return $this->success(null, 'Deleted successfully');
    }

    public function changeStage(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
        ]);

        $deal = $this->dealService->changeStage($deal, $validated['pipeline_stage_id'], $request->user()->id);

        return $this->success($deal->load('stage'), 'Stage updated');
    }

    public function storeNote(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'note' => 'required|string',
        ]);

        $note = $this->dealService->addNote($deal, $validated, $request->user()->id);

        return $this->success($note, 'Note added', 201);
    }

    public function history(Deal $deal)
    {
        return $this->success($this->dealService->getHistory($deal));
    }
}
