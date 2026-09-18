<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageSetting extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'default_driver', 'max_file_size', 'allowed_types',
        's3_key', 's3_secret', 's3_region', 's3_bucket', 's3_url',
        'oss_access_key_id', 'oss_access_key_secret', 'oss_endpoint', 'oss_bucket', 'oss_url', 'oss_is_cname',
        'cos_app_id', 'cos_secret_id', 'cos_secret_key', 'cos_region', 'cos_bucket', 'cos_url',
    ];

    protected $casts = [
        'allowed_types' => 'array',
        'oss_is_cname' => 'boolean',
    ];

    protected $hidden = [
        's3_secret', 'oss_access_key_secret', 'cos_secret_key',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * 获取当前存储驱动配置
     */
    public function getDriverConfig(): array
    {
        return match ($this->default_driver) {
            's3' => [
                'driver' => 's3',
                'key' => $this->s3_key,
                'secret' => $this->s3_secret,
                'region' => $this->s3_region,
                'bucket' => $this->s3_bucket,
                'url' => $this->s3_url,
            ],
            'oss' => [
                'driver' => 'oss',
                'access_id' => $this->oss_access_key_id,
                'access_key' => $this->oss_access_key_secret,
                'endpoint' => $this->oss_endpoint,
                'bucket' => $this->oss_bucket,
                'url' => $this->oss_url,
                'is_cname' => $this->oss_is_cname,
            ],
            'cos' => [
                'driver' => 'cos',
                'app_id' => $this->cos_app_id,
                'secret_id' => $this->cos_secret_id,
                'secret_key' => $this->cos_secret_key,
                'region' => $this->cos_region,
                'bucket' => $this->cos_bucket,
                'url' => $this->cos_url,
            ],
            default => [
                'driver' => 'local',
            ],
        };
    }
}
