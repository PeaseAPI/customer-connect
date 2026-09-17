<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Award extends Model
{
    use HasCompanyScope, SoftDeletes, HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'award_icon_id', 'title', 'description',
        'award_date', 'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'award_date' => 'date',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function awardIcon(): BelongsTo { return $this->belongsTo(AwardIcon::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
}
