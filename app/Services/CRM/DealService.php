<?php

namespace App\Services\CRM;

use App\Models\Deal;
use App\Models\DealHistory;
use Illuminate\Support\Facades\DB;

class DealService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Deal::with(['stage', 'client', 'agent', 'currency']);

        if (!empty($filters['search'])) {
            $query->where('deal_name', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['pipeline_stage_id'])) {
            $query->where('pipeline_stage_id', $filters['pipeline_stage_id']);
        }
        if (!empty($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }

        return $query->orderBy('column_priority')->paginate($perPage);
    }

    public function create(array $data, int $userId): Deal
    {
        return DB::transaction(function () use ($data, $userId) {
            $deal = Deal::create($data);

            DealHistory::create([
                'company_id' => $deal->company_id,
                'deal_id' => $deal->id,
                'pipeline_stage_id' => $deal->pipeline_stage_id,
                'type' => 'created',
                'detail' => 'Deal created',
                'added_by' => $userId,
            ]);

            return $deal;
        });
    }

    public function update(Deal $deal, array $data, int $userId): Deal
    {
        return DB::transaction(function () use ($deal, $data, $userId) {
            $oldStageId = $deal->pipeline_stage_id;
            $deal->update($data);

            if (isset($data['pipeline_stage_id']) && $data['pipeline_stage_id'] != $oldStageId) {
                DealHistory::create([
                    'company_id' => $deal->company_id,
                    'deal_id' => $deal->id,
                    'pipeline_stage_id' => $deal->pipeline_stage_id,
                    'from_stage_id' => $oldStageId,
                    'type' => 'stage_change',
                    'detail' => 'Deal stage changed',
                    'added_by' => $userId,
                ]);
            }

            return $deal->fresh();
        });
    }

    public function delete(Deal $deal): bool
    {
        return $deal->delete();
    }

    public function changeStage(Deal $deal, int $newStageId, int $userId): Deal
    {
        return DB::transaction(function () use ($deal, $newStageId, $userId) {
            $oldStageId = $deal->pipeline_stage_id;
            $deal->update(['pipeline_stage_id' => $newStageId]);

            DealHistory::create([
                'company_id' => $deal->company_id,
                'deal_id' => $deal->id,
                'pipeline_stage_id' => $newStageId,
                'from_stage_id' => $oldStageId,
                'type' => 'stage_change',
                'detail' => 'Deal stage changed via pipeline',
                'added_by' => $userId,
            ]);

            return $deal->fresh();
        });
    }

    public function addNote(Deal $deal, array $data, int $userId)
    {
        return $deal->notes()->create([
            ...$data,
            'company_id' => $deal->company_id,
            'added_by' => $userId,
        ]);
    }

    public function getHistory(Deal $deal, int $perPage = 15)
    {
        return $deal->histories()->with(['stage', 'fromStage', 'creator'])->paginate($perPage);
    }
}
