<?php

namespace App\Http\Controllers\Api;

use App\Exports\LeadExport;
use App\Jobs\ExportDataJob;
use App\Models\Lead;
use App\Services\CRM\LeadService;
use Illuminate\Http\Request;

class LeadController extends BaseApiController
{
    public function __construct(protected LeadService $leadService) {}

    public function index(Request $request)
    {
        $leads = $this->leadService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($leads);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_name' => 'required|string|max:191',
            'lead_email' => 'nullable|email|max:191',
            'lead_mobile' => 'nullable|string|max:20',
            'lead_address' => 'nullable|string',
            'agent_id' => 'nullable|exists:users,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'status_id' => 'nullable|exists:lead_stages,id',
            'pipeline_stage_id' => 'nullable|exists:pipeline_stages,id',
            'value' => 'nullable|numeric',
            'next_follow_up' => 'nullable|date',
            'client_id' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        $lead = $this->leadService->create($validated);

        return $this->success($lead->load(['agent', 'source', 'status', 'pipelineStage']), 'Lead created successfully', 201);
    }

    public function show(Lead $lead)
    {
        return $this->success($lead->load([
            'agent', 'source', 'status', 'pipelineStage', 'contacts',
            'followUps.creator', 'convertedClient', 'creator',
        ]));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'lead_name' => 'sometimes|string|max:191',
            'lead_email' => 'nullable|email|max:191',
            'lead_mobile' => 'nullable|string|max:20',
            'lead_address' => 'nullable|string',
            'agent_id' => 'nullable|exists:users,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'status_id' => 'sometimes|exists:lead_stages,id',
            'pipeline_stage_id' => 'nullable|exists:pipeline_stages,id',
            'value' => 'nullable|numeric',
            'next_follow_up' => 'nullable|date',
        ]);

        $statusId = $validated['status_id'] ?? null;
        unset($validated['status_id']);

        if (!empty($validated)) {
            $lead = $this->leadService->update($lead, $validated);
        }

        if ($statusId) {
            $lead = $this->leadService->changeStatus($lead, (int) $statusId);
        }

        return $this->success($lead->load(['agent', 'source', 'status', 'pipelineStage']), 'Updated successfully');
    }

    public function destroy(Lead $lead)
    {
        $this->leadService->delete($lead);
        return $this->success(null, 'Deleted successfully');
    }

    public function convert(Lead $lead)
    {
        if ($lead->is_client) {
            return $this->error('This lead has already been converted', 400);
        }

        $client = $this->leadService->convertToClient($lead);

        return $this->success($client->load('clientDetail'), 'Lead converted to client', 201);
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status_id', 'source_id', 'agent_id']);
        $filePath = 'exports/leads_' . now()->format('YmdHis') . '.xlsx';

        ExportDataJob::dispatch(
            new LeadExport($filters, $request->attributes->get('company_id')),
            $filePath,
            $request->user()->id
        );

        return $this->success(['file_path' => $filePath], 'Export task submitted, you will be notified when complete');
    }

    /**
     * 线索导入（CSV/Excel）
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $path = $request->file('file')->store('imports');
        $companyId = $request->attributes->get('company_id');

        \App\Jobs\ImportDataJob::dispatch(
            new \App\Imports\LeadImport($companyId),
            $path,
            $request->user()->id
        );

        return $this->success(null, 'Import task submitted, you will be notified when complete');
    }
}
