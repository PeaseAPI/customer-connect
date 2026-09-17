<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Services\Event\EventService;
use Illuminate\Http\Request;

class EventController extends BaseApiController
{
    public function __construct(protected EventService $eventService) {}

    public function index(Request $request)
    {
        $events = $this->eventService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($events);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name' => 'required|string|max:191',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after:start_date_time',
            'location' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'label' => 'nullable|string|max:20',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->eventService->create($validated)->load(['creator']),
            '事件创建成功',
            201
        );
    }

    public function show(Event $event)
    {
        return $this->success($event->load(['creator']));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'event_name' => 'sometimes|string|max:191',
            'start_date_time' => 'sometimes|date',
            'end_date_time' => 'sometimes|date|after:start_date_time',
            'location' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'label' => 'nullable|string|max:20',
        ]);

        $event = $this->eventService->update($event, $validated);

        return $this->success($event->load(['creator']), '更新成功');
    }

    public function destroy(Event $event)
    {
        $this->eventService->delete($event);
        return $this->success(null, '删除成功');
    }
}
