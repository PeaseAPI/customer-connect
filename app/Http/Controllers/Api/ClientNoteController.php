<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientNote;
use App\Services\CRM\ClientNoteService;
use Illuminate\Http\Request;

class ClientNoteController extends BaseApiController
{
    public function __construct(protected ClientNoteService $clientNoteService) {}

    public function index(Request $request, $clientId)
    {
        $notes = $this->clientNoteService->list($clientId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($notes);
    }

    public function store(Request $request, $clientId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'nullable|boolean',
            'member_id' => 'nullable|exists:users,id',
            'is_client_show' => 'nullable|boolean',
            'ask_password' => 'nullable|boolean',
            'details' => 'nullable|string',
        ]);

        $validated['client_id'] = $clientId;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->clientNoteService->create($validated)->load(['member', 'creator']),
            'Client note created',
            201
        );
    }

    public function show($clientId, ClientNote $note)
    {
        return $this->success($note->load(['member', 'creator']));
    }

    public function update(Request $request, $clientId, ClientNote $note)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'nullable|boolean',
            'member_id' => 'nullable|exists:users,id',
            'is_client_show' => 'nullable|boolean',
            'ask_password' => 'nullable|boolean',
            'details' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $note = $this->clientNoteService->update($note, $validated);

        return $this->success($note->load(['member', 'creator']), 'Updated successfully');
    }

    public function destroy($clientId, ClientNote $note)
    {
        $this->clientNoteService->delete($note);

        return $this->success(null, 'Deleted successfully');
    }
}
