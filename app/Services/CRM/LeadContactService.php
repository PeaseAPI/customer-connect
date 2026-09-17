<?php

namespace App\Services\CRM;

use App\Models\LeadContact;

class LeadContactService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = LeadContact::with(['lead', 'creator']);

        if (!empty($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('contact_name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): LeadContact
    {
        return LeadContact::create($data);
    }

    public function update(LeadContact $contact, array $data): LeadContact
    {
        $contact->update($data);
        return $contact->fresh();
    }

    public function delete(LeadContact $contact): bool
    {
        return $contact->delete();
    }
}
