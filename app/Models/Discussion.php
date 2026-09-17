<?php

namespace App\Models;

use App\Traits\HasComments;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discussion extends Model
{
    use HasCompanyScope, HasComments, SoftDeletes, HasFactory;

    protected $fillable = [
        'company_id', 'title', 'description', 'category_id', 'created_by',
        'is_locked', 'is_pinned', 'is_announcement', 'best_solution_id',
        'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_pinned' => 'boolean',
        'is_announcement' => 'boolean',
    ];

    public function category(): BelongsTo { return $this->belongsTo(DiscussionCategory::class, 'category_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function replies(): HasMany { return $this->hasMany(DiscussionReply::class); }
}