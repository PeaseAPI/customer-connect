<?php

namespace App\Services\HRM;

use App\Models\Appreciation;

class AppreciationService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Appreciation::with(['user', 'creator']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Appreciation
    {
        return Appreciation::create($data);
    }

    public function update(Appreciation $appreciation, array $data): Appreciation
    {
        $appreciation->update($data);
        return $appreciation->fresh();
    }

    public function delete(Appreciation $appreciation): bool
    {
        return $appreciation->delete();
    }
}
