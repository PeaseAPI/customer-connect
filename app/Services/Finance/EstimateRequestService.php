<?php

namespace App\Services\Finance;

use App\Models\EstimateRequest;

class EstimateRequestService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = EstimateRequest::with(['client', 'estimate', 'creator']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): EstimateRequest
    {
        return EstimateRequest::create($data);
    }

    public function update(EstimateRequest $estimateRequest, array $data): EstimateRequest
    {
        $estimateRequest->update($data);
        return $estimateRequest->fresh();
    }

    public function delete(EstimateRequest $estimateRequest): bool
    {
        return $estimateRequest->delete();
    }

    public function convert(EstimateRequest $estimateRequest, int $estimateId): EstimateRequest
    {
        $estimateRequest->update([
            'status' => 'converted',
            'estimate_id' => $estimateId,
        ]);
        return $estimateRequest->fresh();
    }
}
