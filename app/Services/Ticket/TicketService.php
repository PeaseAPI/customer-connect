<?php

namespace App\Services\Ticket;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Ticket::with(['agent', 'client', 'creator']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['channel_id'])) {
            $query->where('channel_id', $filters['channel_id']);
        }
        if (!empty($filters['type_id'])) {
            $query->where('type_id', $filters['type_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('subject', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }

    public function update(Ticket $ticket, array $data): Ticket
    {
        // Status and agent changes must go through changeStatus() and assign()
        unset($data['status'], $data['agent_id']);

        $ticket->update($data);
        return $ticket->fresh();
    }

    public function changeStatus(Ticket $ticket, string $status): Ticket
    {
        $ticket->update(['status' => $status]);
        return $ticket->fresh();
    }

    public function assign(Ticket $ticket, ?int $agentId): Ticket
    {
        $ticket->update(['agent_id' => $agentId]);
        return $ticket->fresh();
    }

    public function delete(Ticket $ticket): bool
    {
        return DB::transaction(function () use ($ticket) {
            $ticket->replies()->delete();
            return $ticket->delete();
        });
    }

        public function listReplies(int $ticketId, int $perPage = 15)
    {
        return TicketReply::where('ticket_id', $ticketId)
            ->with(['user', 'creator'])
            ->latest()
            ->paginate($perPage);
    }

    public function createReply(int $ticketId, array $data): TicketReply
    {
        return TicketReply::create([
            'ticket_id' => $ticketId,
            ...$data,
        ]);
    }

    public function updateReply(TicketReply $reply, array $data): TicketReply
    {
        $reply->update($data);
        return $reply->fresh();
    }

    public function deleteReply(TicketReply $reply): bool
    {
        return $reply->delete();
    }
}
