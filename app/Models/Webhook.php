<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Webhook extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'name', 'url', 'secret', 'events',
        'is_active', 'last_triggered_at', 'failure_count',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'failure_count' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Webhook $webhook) {
            if (empty($webhook->secret)) {
                $webhook->secret = Str::random(32);
            }
        });
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    /**
     * 生成签名
     */
    public function generateSignature(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload), $this->secret);
    }

    /**
     * 检查是否订阅了某事件
     */
    public function isSubscribedTo(string $event): bool
    {
        return in_array('*', $this->events) || in_array($event, $this->events);
    }

    /**
     * 记录失败并检查是否应该自动禁用
     */
    public function recordFailure(): void
    {
        $this->increment('failure_count');

        // 连续失败 10 次自动禁用
        if ($this->failure_count >= 10) {
            $this->update(['is_active' => false]);
        }
    }

    /**
     * 重置失败计数
     */
    public function resetFailureCount(): void
    {
        $this->update(['failure_count' => 0]);
    }
}
