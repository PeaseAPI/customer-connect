<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReply extends Model
{
    use HasFactory, HasCompanyScope, HasFiles;

    protected $fillable = [
        'company_id',
        'ticket_id',
        'user_id',
        'message',
    ];

    protected $casts = ['is_internal' => 'boolean'];

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
