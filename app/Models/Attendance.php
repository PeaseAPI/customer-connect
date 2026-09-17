<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'date', 'clock_in_time', 'clock_out_time',
        'clock_in_ip', 'clock_out_ip', 'late', 'late_by', 'half_day',
        'shift_id', 'working_from',
    ];

    protected $casts = ['date' => 'date', 'clock_in_time' => 'datetime', 'clock_out_time' => 'datetime', 'late_by' => 'integer'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function shift(): BelongsTo { return $this->belongsTo(Shift::class); }
}
