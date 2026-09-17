<?php

namespace App\Services\Event;

use App\Models\Event;

class EventService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Event::with(['creator']);

        if (!empty($filters['search'])) {
            $query->where('event_name', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['from_date'])) {
            $query->where('start_date_time', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->where('end_date_time', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        return $event->fresh();
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }
}
