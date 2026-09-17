<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
        use HasCompanyScope, HasFiles, HasFactory;

    protected $fillable = [
        'company_id',
        'event_name',
        'description',
        'location',
        'start_date_time',
        'end_date_time',
        'repeat',
        'repeat_every',
        'repeat_type',
        'repeat_until',
        'created_by',
    ];

    protected $casts = [
        'start_date_time' => 'datetime', 'end_date_time' => 'datetime',
        'repeat_until' => 'date',
        'repeat_every' => 'integer',
    ];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function participants(): BelongsToMany { return $this->belongsToMany(User::class, 'event_participants'); }
}
