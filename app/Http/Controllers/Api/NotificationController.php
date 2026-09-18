<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $notifications = $this->notificationService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($notifications);
    }

    public function update(Request $request, Notification $notification)
    {
        $this->notificationService->markAsRead($notification);
        return $this->success(null, '已标记已读');
    }

    public function destroy(Notification $notification)
    {
        $this->notificationService->delete($notification);
        return $this->success(null, 'Deleted');
    }

    public function markAllRead()
    {
        $this->notificationService->markAllRead();
        return $this->success(null, '全部标记已读');
    }

    public function unreadCount()
    {
        return $this->success([
            'count' => $this->notificationService->unreadCount(),
        ]);
    }
}
