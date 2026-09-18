<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'name', 'key', 'permissions',
        'last_used_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['key'];

    /**
     * 生成新的 API Key
     */
    public static function generateKey(): string
    {
        return 'cc_' . Str::random(48);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 检查密钥是否有效
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * 检查是否有权限访问指定模块
     */
    public function hasPermission(string $module): bool
    {
        if (empty($this->permissions)) {
            return true; // 无限制
        }

        return in_array($module, $this->permissions) || in_array('*', $this->permissions);
    }
}
