<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id', 'working_days', 'late_mark_after',
        'enable_ip_blocking', 'allowed_ips',
    ];

    protected $casts = ['late_mark_after' => 'integer', 'enable_ip_blocking' => 'boolean'];
}