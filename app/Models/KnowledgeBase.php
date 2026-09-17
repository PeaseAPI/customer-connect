<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeBase extends Model
{
    use HasCompanyScope, SoftDeletes, HasFactory;

    protected $fillable = [
        'company_id', 'category_id', 'title', 'description', 'status',
        'added_by', 'last_updated_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function category(): BelongsTo { return $this->belongsTo(KnowledgeBaseCategory::class, 'category_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
}