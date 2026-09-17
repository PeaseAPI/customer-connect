<?php

namespace App\Services\Notice;

use App\Models\Notice;
use App\Models\NoticeView;

class NoticeService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Notice::with('creator');

        if (!empty($filters['search'])) {
            $query->where('heading', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Notice
    {
        return Notice::create($data);
    }

    public function update(Notice $notice, array $data): Notice
    {
        $notice->update($data);
        return $notice->fresh();
    }

    public function delete(Notice $notice): bool
    {
        return $notice->delete();
    }

    public function markAsRead(Notice $notice, int $userId): void
    {
        NoticeView::firstOrCreate([
            'notice_id' => $notice->id,
            'user_id' => $userId,
            'company_id' => $notice->company_id,
        ]);
    }
}
