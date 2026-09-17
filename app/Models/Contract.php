<?php

namespace App\Models;

use App\Enums\ContractStatus;
use App\Traits\HasComments;
use App\Traits\HasCompanyScope;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
            use HasCompanyScope, SoftDeletes, HasFiles, HasComments, HasCustomFields, HasFactory;

        protected $fillable = [
        'company_id', 'client_id', 'subject', 'contract_type_id', 'value',
        'start_date', 'end_date', 'description', 'hash', 'status',
        'signature', 'signed_on', 'amount', 'currency_id', 'contract_detail',
        'contract_number', 'original_contract_number', 'project_id',
        'company_address_id', 'company_sign', 'sign_date', 'created_by',
    ];

        protected $casts = [
        'value' => 'decimal:2', 'amount' => 'decimal:2',
        'start_date' => 'date', 'end_date' => 'date',
        'signed_on' => 'date', 'sign_date' => 'date',
        'status' => ContractStatus::class,
    ];

        public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function contractType(): BelongsTo { return $this->belongsTo(ContractType::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function signature(): HasOne { return $this->hasOne(ContractSignature::class, 'contract_id'); }
    public function discussions(): HasMany { return $this->hasMany(ContractDiscussion::class)->orderByDesc('id'); }
    public function renewHistory(): HasMany { return $this->hasMany(ContractRenewHistory::class, 'contract_id')->orderByDesc('id'); }
}
