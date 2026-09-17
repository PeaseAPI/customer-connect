<?php

namespace App\Services\HRM;

use App\Models\Award;

class AwardService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Award::with(['user', 'awardIcon', 'creator']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Award
    {
        return Award::create($data);
    }

    public function update(Award $award, array $data): Award
    {
        $award->update($data);
        return $award->fresh();
    }

    public function delete(Award $award): bool
    {
        return $award->delete();
    }
}
