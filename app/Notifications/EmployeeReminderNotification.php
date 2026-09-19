<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * 通用员工提醒通知(考勤/周报/文档/费用/跟进/订阅等后台命令使用)。
 * 写入自定义notifications表(title/message/channel), data列由database通道写入 toDatabase 返回值。
 */
class EmployeeReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public string $title, public array $payload = []) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge(['title' => $this->title], $this->payload);
    }
}