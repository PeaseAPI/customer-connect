<?php

namespace App\Services\Notification;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Notification::where('user_id', Auth::id());

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (!empty($filters['unread'])) {
            $query->whereNull('read_at');
        }

        return $query->latest()->paginate($perPage);
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->update(['read_at' => now()]);
    }

    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function unreadCount(): int
    {
        return Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();
    }
}
