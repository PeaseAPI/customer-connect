<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Services\Event\EventService;
use App\Events\EventReminderSent;
use App\Events\EventInviteSent;
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
            'repeat' => 'sometimes|in:no,daily,weekly,monthly,yearly,custom',
            'repeat_every' => 'nullable|integer|min:1',
            'repeat_type' => 'nullable|in:daily,weekly,monthly,yearly',
            'repeat_until' => 'nullable|date|after:end_date_time',
            'reminder_minutes' => 'nullable|integer|in:5,10,15,30,60,1440',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'exists:users,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        $participantIds = $validated['participant_ids'] ?? [];
        unset($validated['participant_ids']);

        $reminderMinutes = $validated['reminder_minutes'] ?? null;
        unset($validated['reminder_minutes']);

        $event = $this->eventService->create($validated);

        // 同步参与者
        if (!empty($participantIds)) {
            $event->participants()->sync($participantIds);
        }

        // Settings提醒
        if ($reminderMinutes) {
            app(\App\Services\Event\RecurringEventService::class)->setReminder($event, $reminderMinutes);
        }

        return $this->success(
            $event->load(['creator', 'participants']),
            'Event created',
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

        return $this->success($event->load(['creator']), 'Updated successfully');
    }

        public function destroy(Event $event)
    {
        $this->eventService->delete($event);
        return $this->success(null, 'Deleted successfully');
    }

    /**
     * 添加参与者
     */
    public function addParticipants(Request $request, Event $event)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $event->participants()->syncWithoutDetaching($validated['user_ids']);

        return $this->success($event->load(['creator', 'participants']), 'Participant added successfully');
    }

    /**
     * 移除参与者
     */
    public function removeParticipant(Event $event, $user)
    {
        $event->participants()->detach($user);

        return $this->success(null, 'Participant removed successfully');
    }
}
