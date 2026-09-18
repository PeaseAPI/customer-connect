<?php

namespace App\Http\Controllers\Api;

use App\Models\PipelineStage;
use App\Models\LeadPipeline;
use App\Services\CRM\PipelineService;
use Illuminate\Http\Request;

class PipelineController extends BaseApiController
{
    public function __construct(protected PipelineService $pipelineService) {}

    public function index()
    {
        return $this->success($this->pipelineService->list());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'label_color' => 'nullable|string|max:20',
            'default' => 'nullable|boolean',
        ]);

        $pipeline = $this->pipelineService->create($validated, $request->user()->id);

        return $this->success($pipeline->load('stages'), 'Pipeline created successfully', 201);
    }

    public function show(LeadPipeline $pipeline)
    {
        return $this->success($pipeline->load('stages'));
    }

    public function update(Request $request, LeadPipeline $pipeline)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'label_color' => 'nullable|string|max:20',
            'default' => 'nullable|boolean',
        ]);

        $pipeline = $this->pipelineService->update($pipeline, $validated);

        return $this->success($pipeline->load('stages'), 'Updated successfully');
    }

    public function destroy(LeadPipeline $pipeline)
    {
        $this->pipelineService->delete($pipeline);
        return $this->success(null, 'Deleted successfully');
    }

    public function storeStage(Request $request, LeadPipeline $pipeline)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'type' => 'required|in:lead,won,lost',
            'label_color' => 'nullable|string|max:20',
            'priority' => 'nullable|integer',
        ]);

        $stage = $this->pipelineService->createStage($pipeline, $validated, $request->user()->id);
        return $this->success($stage, 'Stage created successfully', 201);
    }

    public function updateStage(Request $request, PipelineStage $stage)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'type' => 'sometimes|in:lead,won,lost',
            'label_color' => 'nullable|string|max:20',
            'priority' => 'nullable|integer',
        ]);

        $stage = $this->pipelineService->updateStage($stage, $validated);
        return $this->success($stage, 'Stage updated');
    }

    public function destroyStage(PipelineStage $stage)
    {
        $this->pipelineService->deleteStage($stage);
        return $this->success(null, 'Stage deleted successfully');
    }
}
