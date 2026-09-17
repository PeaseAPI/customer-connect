<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\Priority;
use App\Traits\HasCompanyScope;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasCustomFields, HasFactory;

    protected $fillable = [
        'company_id',
        'subject',
        'description',
        'priority',
        'client_id',
        'agent_id',
        'status',
        'created_by',
    ];

    protected $casts = ['tags' => 'array', 'status' => TicketStatus::class, 'priority' => Priority::class];

    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function agent(): BelongsTo { return $this->belongsTo(User::class, 'agent_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function replies(): HasMany { return $this->hasMany(TicketReply::class); }
}
