<?php

namespace App\Models;

use App\Enums\ActiveInactiveStatus;
use App\Traits\HasCompanyScope;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasCompanyScope, HasFiles, SoftDeletes, HasFactory;

    protected $fillable = [
        'company_id', 'heading', 'description', 'to', 'notice_date',
        'expiry_date', 'status', 'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'notice_date' => 'date',
        'expiry_date' => 'date',
        'status' => ActiveInactiveStatus::class,
    ];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
    public function views(): HasMany { return $this->hasMany(NoticeView::class); }
}