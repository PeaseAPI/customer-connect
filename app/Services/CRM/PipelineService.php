<?php

namespace App\Services\CRM;

use App\Models\LeadPipeline;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\DB;

class PipelineService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        return LeadPipeline::with('stages')
            ->when(isset($filters['search']) && $filters['search'] !== '', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%");
            })
            ->orderBy('priority')
            ->paginate($perPage);
    }

    /**
     * Full unpaginated list (used for dropdown/option lists, e.g. deals stage filter).
     */
    public function listAll()
    {
        return LeadPipeline::with('stages')->orderBy('priority')->get();
    }

    public function create(array $data, int $userId): LeadPipeline
    {
        return DB::transaction(function () use ($data, $userId) {
            $pipeline = LeadPipeline::create([...$data, 'added_by' => $userId]);

            $defaultStages = [
                ['name' => 'New', 'type' => 'lead', 'priority' => 1, 'label_color' => '#337ab7', 'default' => true],
                ['name' => 'Contacted', 'type' => 'lead', 'priority' => 2, 'label_color' => '#5cb85c'],
                ['name' => 'Proposal', 'type' => 'lead', 'priority' => 3, 'label_color' => '#f0ad4e'],
                ['name' => 'Won', 'type' => 'won', 'priority' => 4, 'label_color' => '#5cb85c'],
                ['name' => 'Lost', 'type' => 'lost', 'priority' => 5, 'label_color' => '#d9534f'],
            ];

            foreach ($defaultStages as $stage) {
                $pipeline->stages()->create([...$stage, 'company_id' => $pipeline->company_id, 'added_by' => $userId]);
            }

            return $pipeline;
        });
    }

    public function update(LeadPipeline $pipeline, array $data): LeadPipeline
    {
        $pipeline->update($data);
        return $pipeline->fresh();
    }

    public function delete(LeadPipeline $pipeline): bool
    {
        return $pipeline->delete();
    }

    public function createStage(LeadPipeline $pipeline, array $data, int $userId): PipelineStage
    {
        return $pipeline->stages()->create([...$data, 'company_id' => $pipeline->company_id, 'added_by' => $userId]);
    }

    public function updateStage(PipelineStage $stage, array $data): PipelineStage
    {
        $stage->update($data);
        return $stage->fresh();
    }

    public function deleteStage(PipelineStage $stage): bool
    {
        return $stage->delete();
    }
}
