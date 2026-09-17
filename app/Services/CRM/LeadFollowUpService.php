<?php

namespace App\Services\CRM;

use App\Models\LeadFollowUp;

class LeadFollowUpService
{
    public function list(int $leadId, array $filters = [], int $perPage = 15)
    {
        $query = LeadFollowUp::where('lead_id', $leadId)->with(['lead', 'addedBy']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): LeadFollowUp
    {
        return LeadFollowUp::create($data);
    }

    public function update(LeadFollowUp $followUp, array $data): LeadFollowUp
    {
        $followUp->update($data);
        return $followUp->fresh();
    }

    public function delete(LeadFollowUp $followUp): bool
    {
        return $followUp->delete();
    }
}
