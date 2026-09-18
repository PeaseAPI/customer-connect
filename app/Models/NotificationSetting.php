<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'channel',
        'type',
        'enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'sound_enabled',
        'desktop_enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sound_enabled' => 'boolean',
        'desktop_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 检查用户是否启用了某种通知类型
     */
    public static function isEnabledForUser(int $userId, string $type, string $channel = 'database'): bool
    {
        $setting = static::where('user_id', $userId)
            ->where('type', $type)
            ->where('channel', $channel)
            ->first();

        // 默认启用
        return $setting ? $setting->enabled : true;
    }

    /**
     * 检查当前是否在静默时段内
     */
    public function isQuietHoursNow(): bool
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }

        $now = now()->format('H:i');
        return $now >= $this->quiet_hours_start && $now <= $this->quiet_hours_end;
    }
}
