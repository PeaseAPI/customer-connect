<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'package_id',
        'ends_at',
        'trial_ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime', 'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime', 'cancelled_at' => 'datetime',
        'status' => SubscriptionStatus::class,
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function package(): BelongsTo { return $this->belongsTo(Package::class); }
    public function isActive(): bool { return $this->status === SubscriptionStatus::Active && (!$this->ends_at || $this->ends_at->isFuture()); }
}