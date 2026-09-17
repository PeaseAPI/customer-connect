<?php

namespace App\Services\Company;

use App\Models\StickyNote;

class StickyNoteService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = StickyNote::with(['user', 'creator']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['search'])) {
            $query->where('note_text', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): StickyNote
    {
        if (empty($data['user_id'])) {
            $data['user_id'] = $data['added_by'];
        }

        return StickyNote::create($data);
    }

    public function update(StickyNote $stickyNote, array $data): StickyNote
    {
        $stickyNote->update($data);
        return $stickyNote->fresh();
    }

    public function delete(StickyNote $stickyNote): bool
    {
        return $stickyNote->delete();
    }
}
