<?php

namespace App\Services\CRM;

use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Events\LeadCreated;
use App\Events\LeadConverted;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Lead::with(['agent', 'source', 'status', 'pipelineStage']);

        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }
        if (!empty($filters['source_id'])) {
            $query->where('source_id', $filters['source_id']);
        }
        if (!empty($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('lead_name', 'like', "%{$filters['search']}%")
                  ->orWhere('lead_email', 'like', "%{$filters['search']}%")
                  ->orWhere('lead_mobile', 'like', "%{$filters['search']}%");
            });
        }
        if (!empty($filters['created_at_start'])) {
            $query->where('created_at', '>=', $filters['created_at_start']);
        }
        if (!empty($filters['created_at_end'])) {
            $query->where('created_at', '<=', $filters['created_at_end']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $lead = Lead::create($data);
            if (!empty($data['tags'])) {
                $lead->tags()->sync($data['tags']);
            }
            event(new LeadCreated($lead));
            return $lead;
        });
    }

    public function update(Lead $lead, array $data): Lead
    {
        return DB::transaction(function () use ($lead, $data) {
            // Status changes must go through changeStatus() to dispatch events
            unset($data['status_id']);

            $lead->update($data);
            if (isset($data['tags'])) {
                $lead->tags()->sync($data['tags']);
            }
            return $lead->fresh();
        });
    }

    public function changeStatus(Lead $lead, int $statusId): Lead
    {
        $lead->update(['status_id' => $statusId]);
        return $lead->fresh();
    }

    public function delete(Lead $lead): bool
    {
        return $lead->delete();
    }

    public function convertToClient(Lead $lead, array $clientData = []): \App\Models\User
    {
        return DB::transaction(function () use ($lead, $clientData) {
            $clientData = array_merge([
                'name' => $lead->lead_name,
                'email' => $lead->lead_email ?? 'lead_' . $lead->id . '@converted.com',
                'mobile' => $lead->lead_mobile,
                'source_id' => $lead->source_id,
                'agent_id' => $lead->agent_id,
                'company_id' => $lead->company_id,
                'password' => bcrypt(str()->random(16)),
            ], $clientData);

            $client = \App\Models\User::create($clientData);
            $client->assignRole('client');

            $lead->update(['is_client' => true, 'client_converted_id' => $client->id]);
            event(new LeadConverted($lead, $client));
            return $client;
        });
    }

    public function addFollowUp(Lead $lead, array $data): LeadFollowUp
    {
        return LeadFollowUp::create(array_merge($data, [
            'lead_id' => $lead->id,
            'company_id' => $lead->company_id,
        ]));
    }

    public function assign(Lead $lead, int $newAgentId): Lead
    {
        $lead->update(['agent_id' => $newAgentId]);
        return $lead->fresh();
    }

    public function batchUpdateStatus(array $leadIds, string $status): int
    {
        return Lead::whereIn('id', $leadIds)->update(['status' => $status]);
    }
}
