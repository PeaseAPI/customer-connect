<?php

namespace App\Services\Finance;

use App\Models\OfflinePaymentMethod;

class OfflinePaymentMethodService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = OfflinePaymentMethod::query();

        if (!empty($filters['is_active'])) {
            $query->where('is_active', true);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): OfflinePaymentMethod
    {
        return OfflinePaymentMethod::create($data);
    }

    public function update(OfflinePaymentMethod $method, array $data): OfflinePaymentMethod
    {
        $method->update($data);
        return $method->fresh();
    }

    public function delete(OfflinePaymentMethod $method): bool
    {
        return $method->delete();
    }
}
