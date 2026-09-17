<?php

namespace App\Services\CRM;

use App\Models\ClientNote;

class ClientNoteService
{
    public function list(int $clientId, array $filters = [], int $perPage = 15)
    {
        $query = ClientNote::where('client_id', $clientId)->with(['member', 'creator']);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['is_client_show'])) {
            $query->where('is_client_show', $filters['is_client_show']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ClientNote
    {
        return ClientNote::create($data);
    }

    public function update(ClientNote $note, array $data): ClientNote
    {
        $note->update($data);
        return $note->fresh();
    }

    public function delete(ClientNote $note): bool
    {
        return $note->delete();
    }
}
