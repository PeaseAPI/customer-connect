<?php

namespace App\Services\Discussion;

use App\Models\Discussion;
use App\Models\DiscussionReply;
use Illuminate\Support\Facades\DB;

class DiscussionService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Discussion::with(['category', 'creator', 'replies']);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['pinned'])) {
            $query->where('is_pinned', true);
        }

        return $query->orderByDesc('is_pinned')->latest()->paginate($perPage);
    }

    public function create(array $data): Discussion
    {
        return Discussion::create($data);
    }

    public function update(Discussion $discussion, array $data): Discussion
    {
        $discussion->update($data);
        return $discussion->fresh();
    }

    public function delete(Discussion $discussion): bool
    {
        return $discussion->delete();
    }

    public function createReply(Discussion $discussion, array $data): DiscussionReply
    {
        return $discussion->replies()->create($data);
    }

    public function markSolution(Discussion $discussion, DiscussionReply $reply): Discussion
    {
        $reply->update(['is_solution' => true]);
        $discussion->update(['best_solution_id' => $reply->id]);
        return $discussion->fresh();
    }
}
