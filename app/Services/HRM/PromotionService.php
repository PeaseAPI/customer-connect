<?php

namespace App\Services\HRM;

use App\Models\Promotion;

class PromotionService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Promotion::with(['user', 'designation', 'department', 'creator']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Promotion
    {
        return Promotion::create($data);
    }

    public function update(Promotion $promotion, array $data): Promotion
    {
        $promotion->update($data);
        return $promotion->fresh();
    }

    public function delete(Promotion $promotion): bool
    {
        return $promotion->delete();
    }
}
