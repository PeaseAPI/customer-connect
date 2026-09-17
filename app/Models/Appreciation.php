<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appreciation extends Model
{
    use HasCompanyScope, HasFactory;

            protected $fillable = [
        'company_id', 'user_id', 'award_id', 'description', 'awarded_date', 'awarded_by', 'added_by',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function award(): BelongsTo { return $this->belongsTo(AwardIcon::class, 'award_id'); }
    public function awarder(): BelongsTo { return $this->belongsTo(User::class, 'awarded_by'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'awarded_by'); }
}