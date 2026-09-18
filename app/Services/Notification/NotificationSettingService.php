<?php

namespace App\Services\Notification;

use App\Models\NotificationSetting;
use Illuminate\Support\Facades\Auth;

class NotificationSettingService
{
    /**
     * 获取用户的通知设置（含默认值）
     */
    public function getUserSettings(int $userId, int $companyId): array
    {
        $settings = NotificationSetting::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->get()
            ->groupBy('type')
            ->map(fn($items) => $items->keyBy('channel'))
            ->toArray();

        // 补全默认通知类型
        $defaultTypes = $this->getDefaultNotificationTypes();
        $result = [];
        foreach ($defaultTypes as $type => $label) {
            $result[$type] = [
                'label' => $label,
                'channels' => [],
            ];
            foreach (['database', 'mail', 'sms', 'dingtalk', 'wework', 'feishu'] as $channel) {
                $existing = $settings[$type][$channel] ?? null;
                $result[$type]['channels'][$channel] = [
                    'enabled' => $existing ? $existing['enabled'] : in_array($channel, ['database', 'mail']),
                ];
            }
        }

        // 获取静默时段
        $firstSetting = NotificationSetting::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->first();

        return [
            'types' => $result,
            'quiet_hours_start' => $firstSetting?->quiet_hours_start,
            'quiet_hours_end' => $firstSetting?->quiet_hours_end,
            'sound_enabled' => $firstSetting?->sound_enabled ?? true,
            'desktop_enabled' => $firstSetting?->desktop_enabled ?? true,
        ];
    }

    /**
     * 更新用户通知设置
     */
    public function updateUserSettings(int $userId, int $companyId, array $data): void
    {
        // 更新静默时段
        if (isset($data['quiet_hours_start']) || isset($data['quiet_hours_end']) ||
            isset($data['sound_enabled']) || isset($data['desktop_enabled'])) {
            NotificationSetting::upsert(
                array_filter([
                    'user_id' => $userId,
                    'company_id' => $companyId,
                    'channel' => 'database',
                    'type' => '__global__',
                    'quiet_hours_start' => $data['quiet_hours_start'] ?? null,
                    'quiet_hours_end' => $data['quiet_hours_end'] ?? null,
                    'sound_enabled' => $data['sound_enabled'] ?? true,
                    'desktop_enabled' => $data['desktop_enabled'] ?? true,
                ]),
                ['user_id', 'type', 'channel'],
                ['quiet_hours_start', 'quiet_hours_end', 'sound_enabled', 'desktop_enabled']
            );
        }

        // 更新各类型通知设置
        if (isset($data['types']) && is_array($data['types'])) {
            foreach ($data['types'] as $type => $channels) {
                if (!is_array($channels)) continue;
                foreach ($channels as $channel => $setting) {
                    if (!is_array($setting)) continue;
                    NotificationSetting::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'company_id' => $companyId,
                            'type' => $type,
                            'channel' => $channel,
                        ],
                        [
                            'enabled' => $setting['enabled'] ?? true,
                        ]
                    );
                }
            }
        }
    }

    /**
     * 获取管理员全局通知设置
     */
    public function getCompanySettings(int $companyId): array
    {
        return [
            'channels' => [
                'database' => ['enabled' => true],
                'mail' => ['enabled' => true],
                'sms' => ['enabled' => false],
                'dingtalk' => ['enabled' => false],
                'wework' => ['enabled' => false],
                'feishu' => ['enabled' => false],
            ],
            'admin_force_2fa' => false,
            'notification_templates' => $this->getDefaultNotificationTypes(),
        ];
    }

    /**
     * 默认通知类型列表
     */
    private function getDefaultNotificationTypes(): array
    {
        return [
            'task_assigned' => '任务分配',
            'task_updated' => '任务更新',
            'task_completed' => '任务完成',
            'task_comment' => '任务评论',
            'project_created' => '项目创建',
            'project_updated' => '项目更新',
            'leave_request' => '请假申请',
            'leave_status' => '请假审批结果',
            'attendance_anomaly' => '考勤异常',
            'ticket_created' => '工单创建',
            'ticket_updated' => '工单更新',
            'invoice_created' => '发票创建',
            'payment_received' => '收款通知',
            'contract_expiring' => '合同到期提醒',
            'contract_status' => '合同状态变更',
            'lead_assigned' => '线索分配',
            'lead_follow_up' => '线索跟进提醒',
            'event_reminder' => '事件提醒',
            'birthday_reminder' => '生日提醒',
            'announcement' => '公告通知',
        ];
    }
}
