<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use App\Services\Ticket\TicketService;
use Illuminate\Http\Request;

class TicketController extends BaseApiController
{
    public function __construct(protected TicketService $ticketService) {}

    public function index(Request $request)
    {
        $tickets = $this->ticketService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:191',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'client_id' => 'nullable|exists:users,id',
            'agent_id' => 'nullable|exists:users,id',
            'channel_id' => 'nullable|exists:ticket_channels,id',
            'type_id' => 'nullable|exists:ticket_types,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        $ticket = $this->ticketService->create($validated);

        return $this->success($ticket->load(['agent', 'client', 'creator']), '工单创建成功', 201);
    }

    public function show(Ticket $ticket)
    {
        return $this->success($ticket->load(['agent', 'client', 'creator', 'replies.creator']));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:191',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:open,pending,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'agent_id' => 'sometimes|nullable|exists:users,id',
            'channel_id' => 'nullable|exists:ticket_channels,id',
            'type_id' => 'nullable|exists:ticket_types,id',
        ]);

        $ticket = $this->ticketService->update($ticket, $validated);

        return $this->success($ticket->load(['agent', 'client', 'creator']), '更新成功');
    }

    public function destroy(Ticket $ticket)
    {
        $this->ticketService->delete($ticket);
        return $this->success(null, '删除成功');
    }
}
