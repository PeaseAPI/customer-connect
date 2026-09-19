<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentAuditLog extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'provider',
        'auditable_type', 'auditable_id',
        'content_type', 'content_preview', 'content_url',
        'task_id', 'suggestion', 'risk_level',
        'labels', 'details', 'status', 'audited_at',
    ];

    protected $casts = [
        'labels' => 'array',
        'details' => 'array',
        'audited_at' => 'datetime',
    ];

    /**
     * 关联用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联实体（多态）
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 是否通过
     */
    public function isPassed(): bool
    {
        return $this->suggestion === 'PASS';
    }

    /**
     * 是否被拦截
     */
    public function isBlocked(): bool
    {
        return $this->suggestion === 'BLOCK';
    }

    /**
     * 是否需要复审
     */
    public function needsReview(): bool
    {
        return $this->suggestion === 'REVIEW';
    }

    /**
     * 是否处理中
     */
    public function isProcessing(): bool
    {
        return $this->status === 'PROCESSING';
    }

    /**
     * 审核建议名称
     */
    public function getSuggestionNameAttribute(): string
    {
        return match ($this->suggestion) {
            'PASS' => '通过',
            'REVIEW' => '需复审',
            'BLOCK' => '已拦截',
            'PENDING' => '待处理',
            default => '未知',
        };
    }

    /**
     * 风险等级名称
     */
    public function getRiskLevelNameAttribute(): string
    {
        return match ($this->risk_level) {
            'NONE' => '无风险',
            'LOW' => '低风险',
            'MEDIUM' => '中风险',
            'HIGH' => '高风险',
            default => '未知',
        };
    }

    /**
     * 内容类型名称
     */
    public function getContentTypeNameAttribute(): string
    {
        return match ($this->content_type) {
            'text' => '文本',
            'image' => '图片',
            'video' => '视频',
            'audio' => '音频',
            default => '未知',
        };
    }

    /**
     * 作用域：被拦截的记录
     */
    public function scopeBlocked($query)
    {
        return $query->where('suggestion', 'BLOCK');
    }

    /**
     * 作用域：需复审的记录
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('suggestion', 'REVIEW');
    }

    /**
     * 作用域：处理中
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'PROCESSING');
    }
}
