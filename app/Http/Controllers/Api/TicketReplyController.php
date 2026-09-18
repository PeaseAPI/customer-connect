<?php

namespace App\Http\Controllers\Api;

use App\Models\TicketReply;
use App\Services\Ticket\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketReplyController extends BaseApiController
{
    public function __construct(protected TicketService $ticketService) {}

    public function index(Request $request, $ticketId)
    {
        $replies = $this->ticketService->listReplies($ticketId, $request->per_page ?? 15);
        return $this->paginated($replies);
    }

    public function store(Request $request, $ticketId)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->ticketService->createReply($ticketId, $validated)->load(['user', 'creator']),
            '回复成功',
            201
        );
    }

    public function show($ticketId, TicketReply $reply)
    {
        return $this->success($reply->load(['user', 'creator']));
    }

    public function update(Request $request, $ticketId, TicketReply $reply)
    {
        $validated = $request->validate([
            'message' => 'sometimes|string',
        ]);

        $reply = $this->ticketService->updateReply($reply, $validated);

        return $this->success($reply->load(['user', 'creator']), 'Updated successfully');
    }

    public function destroy($ticketId, TicketReply $reply)
    {
        $this->ticketService->deleteReply($reply);
        return $this->success(null, 'Deleted successfully');
    }
}
