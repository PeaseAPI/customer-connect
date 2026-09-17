<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Traits\HasCompanyScope;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasCompanyScope, SoftDeletes, HasFiles, HasCustomFields, HasFactory;

        protected $fillable = [
        'company_id',
        'item_name',
        'purchase_from',
        'purchase_date',
        'amount',
        'currency_id',
        'category_id',
        'project_id',
        'user_id',
        'status',
        'billable',
        'note',
        'exchange_rate',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date', 'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'billable' => 'boolean', 'status' => ExpenseStatus::class,
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function client(): BelongsTo { return $this->belongsTo(User::class, 'client_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function category(): BelongsTo { return $this->belongsTo(ExpenseCategory::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
