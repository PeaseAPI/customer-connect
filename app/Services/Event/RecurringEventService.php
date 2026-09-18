<?php

namespace App\Services\Event;

use App\Models\Event;
use Carbon\Carbon;

class RecurringEventService
{
    /**
     * 生成循环事件
     * 遍历所有到期需要生成的循环事件，自动创建下一周期的事件实例
     */
    public function generateRecurringEvents(): int
    {
        $created = 0;
        $now = now();

        $recurringEvents = Event::where('repeat', '!=', 'no')
            ->whereNotNull('repeat_until')
            ->where('end_date_time', '<=', $now)
            ->get();

        foreach ($recurringEvents as $event) {
            $newEvent = $this->createNextInstance($event);
            if ($newEvent) {
                $created++;
            }
        }

        return $created;
    }

    /**
     * 为循环事件创建下一个实例
     */
    public function createNextInstance(Event $event): ?Event
    {
        $startDateTime = Carbon::parse($event->start_date_time);
        $endDateTime = Carbon::parse($event->end_date_time);
        $durationMinutes = $startDateTime->diffInMinutes($endDateTime);

        // 计算下一次开始时间
        $nextStart = $this->calculateNextDateTime(
            $startDateTime,
            $event->repeat_every ?? 1,
            $event->repeat_type ?? 'daily'
        );

        if (!$nextStart) {
            return null;
        }

        // 检查是否超过截止日期
        $repeatUntil = $event->repeat_until ? Carbon::parse($event->repeat_until) : null;
        if ($repeatUntil && $nextStart->gt($repeatUntil)) {
            // 更新原事件标记为不再循环
            $event->update(['repeat' => 'no']);
            return null;
        }

        $nextEnd = $nextStart->copy()->addMinutes($durationMinutes);

        // 创建新的事件实例
        $newEvent = Event::create([
            'company_id' => $event->company_id,
            'event_name' => $event->event_name,
            'description' => $event->description,
            'location' => $event->location,
            'start_date_time' => $nextStart,
            'end_date_time' => $nextEnd,
            'repeat' => $event->repeat,
            'repeat_every' => $event->repeat_every,
            'repeat_type' => $event->repeat_type,
            'repeat_until' => $event->repeat_until,
            'created_by' => $event->created_by,
        ]);

        // 复制参与者
        if ($event->participants()->exists()) {
            $newEvent->participants()->sync($event->participants->pluck('id'));
        }

        // 更新原事件的日期为下一次循环，以保持循环链
        $event->update([
            'start_date_time' => $nextStart,
            'end_date_time' => $nextEnd,
        ]);

        return $newEvent;
    }

    /**
     * 设置事件提醒
     */
    public function setReminder(Event $event, int $minutesBefore = 15): void
    {
        // 将提醒时间存入缓存，由调度器检查
        $reminderKey = "event_reminder:{$event->id}";
        $reminderTime = Carbon::parse($event->start_date_time)->subMinutes($minutesBefore);

        if ($reminderTime->isFuture()) {
            cache()->put($reminderKey, [
                'event_id' => $event->id,
                'user_ids' => $event->participants->pluck('id')->push($event->created_by)->unique()->toArray(),
                'remind_at' => $reminderTime->toDateTimeString(),
                'event_name' => $event->event_name,
            ], $reminderTime);
        }
    }

    /**
     * 检查并发送到期的事件提醒
     */
    public function processReminders(): int
    {
        $sent = 0;
        $now = now();

        // 通过事件查询即将开始的事件（未来15分钟内）
        $upcomingEvents = Event::where('start_date_time', '>', $now)
            ->where('start_date_time', '<=', $now->copy()->addMinutes(15))
            ->get();

        foreach ($upcomingEvents as $event) {
            $reminderKey = "event_reminder_sent:{$event->id}";

            // 避免重复发送
            if (cache()->has($reminderKey)) {
                continue;
            }

            $userIds = $event->participants->pluck('id')->push($event->created_by)->unique()->toArray();

            foreach ($userIds as $userId) {
                \App\Models\Notification::create([
                    'company_id' => $event->company_id,
                    'user_id' => $userId,
                    'type' => 'event_reminder',
                    'title' => '事件即将开始',
                    'message' => "「{$event->event_name}」将于{$event->start_date_time->format('H:i')}开始",
                    'data' => [
                        'event_id' => $event->id,
                        'start_time' => $event->start_date_time->toDateTimeString(),
                    ],
                    'channel' => 'database',
                ]);
            }

            // 标记已发送（2小时过期）
            cache()->put($reminderKey, true, now()->addHours(2));
            $sent++;
        }

        return $sent;
    }

    /**
     * 计算下一个循环日期时间
     */
    private function calculateNextDateTime(Carbon $currentDateTime, int $every, string $type): ?Carbon
    {
        return match ($type) {
            'daily' => $currentDateTime->copy()->addDays($every),
            'weekly' => $currentDateTime->copy()->addWeeks($every),
            'monthly' => $currentDateTime->copy()->addMonths($every),
            'yearly' => $currentDateTime->copy()->addYears($every),
            default => $currentDateTime->copy()->addDays($every),
        };
    }
}
