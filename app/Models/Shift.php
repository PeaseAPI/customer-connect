<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'shift_name', 'start_time', 'end_time',
        'half_day_mark_time', 'late_mark_after',
    ];

    protected $casts = ['late_mark_after' => 'integer'];
}
