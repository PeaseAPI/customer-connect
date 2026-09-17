<?php

namespace App\Services\HRM;

use App\Models\EmergencyContact;

class EmergencyContactService
{
    public function list(int $employeeId, array $filters = [], int $perPage = 15)
    {
        $query = EmergencyContact::where('user_id', $employeeId)->with(['creator']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): EmergencyContact
    {
        return EmergencyContact::create($data);
    }

    public function update(EmergencyContact $emergencyContact, array $data): EmergencyContact
    {
        $emergencyContact->update($data);
        return $emergencyContact->fresh();
    }

    public function delete(EmergencyContact $emergencyContact): bool
    {
        return $emergencyContact->delete();
    }
}
