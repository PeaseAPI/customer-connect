<?php

namespace App\Services\HRM;

use App\Models\EmployeeDocumentExpiry;

class EmployeeDocumentExpiryService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = EmployeeDocumentExpiry::with(['document']);

        if (isset($filters['notified'])) {
            $query->where('notified', $filters['notified']);
        }
        if (!empty($filters['expiring_within'])) {
            $days = $filters['expiring_within'];
            $query->whereHas('document', fn($q) => $q->where('expiry_date', '<=', now()->addDays($days)));
        }

        $query->whereHas('document', fn($q) => $q->where('expiry_date', '>=', now()));

        return $query->latest()->paginate($perPage);
    }

    public function update(EmployeeDocumentExpiry $documentExpiry, array $data): EmployeeDocumentExpiry
    {
        $documentExpiry->update($data);
        return $documentExpiry->fresh();
    }

    public function delete(EmployeeDocumentExpiry $documentExpiry): bool
    {
        return $documentExpiry->delete();
    }
}
