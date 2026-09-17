<?php

namespace App\Services\Chat;

use App\Models\Chat;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function list(int $userId, int $perPage = 15)
    {
        return Chat::with(['participants', 'creator', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->whereHas('participants', fn($q) => $q->where('users.id', $userId))
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data, array $participantIds): Chat
    {
        return DB::transaction(function () use ($data, $participantIds) {
            $chat = Chat::create($data);
            $participantIds[] = $data['created_by'];
            $chat->participants()->sync(array_unique($participantIds));
            return $chat;
        });
    }

    public function update(Chat $chat, array $data): Chat
    {
        $chat->update($data);
        return $chat->fresh();
    }

    public function delete(Chat $chat): bool
    {
        return $chat->delete();
    }

    public function sendMessage(Chat $chat, array $data)
    {
        return $chat->messages()->create($data);
    }

    public function addParticipants(Chat $chat, array $participantIds): Chat
    {
        $chat->participants()->syncWithoutDetaching($participantIds);
        return $chat->fresh();
    }

    public function removeParticipant(Chat $chat, int $userId): Chat
    {
        $chat->participants()->detach($userId);
        return $chat->fresh();
    }
}
