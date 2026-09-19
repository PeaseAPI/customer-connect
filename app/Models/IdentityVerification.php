<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class IdentityVerification extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'provider',
        'verifiable_type', 'verifiable_id',
        'type', 'name', 'phone', 'id_number',
        'result', 'carrier', 'request_id',
        'raw_response', 'verified_at',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'verified_at' => 'datetime',
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
    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * 是否认证通过
     */
    public function isMatch(): bool
    {
        return $this->result === 'MATCH';
    }

    /**
     * 是否三要素认证
     */
    public function isThreeFactor(): bool
    {
        return $this->type === 'three_factor';
    }

    /**
     * 运营商名称
     */
    public function getCarrierNameAttribute(): string
    {
        return match ($this->carrier) {
            'CMCC' => '中国移动',
            'CUCC' => '中国联通',
            'CTCC' => '中国电信',
            default => '未知',
        };
    }

    /**
     * 结果名称
     */
    public function getResultNameAttribute(): string
    {
        return match ($this->result) {
            'MATCH' => '认证通过',
            'MISMATCH' => '认证不一致',
            'UNKNOWN' => '查无此人',
            default => '未知',
        };
    }

    /**
     * 脱敏身份证号
     */
    public function getMaskedIdNumberAttribute(): string
    {
        if (!$this->id_number) return '';
        return substr($this->id_number, 0, 4) . '**********' . substr($this->id_number, -4);
    }

    /**
     * 脱敏手机号
     */
    public function getMaskedPhoneAttribute(): string
    {
        return substr($this->phone, 0, 3) . '****' . substr($this->phone, -4);
    }
}
