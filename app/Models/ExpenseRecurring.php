<?php

namespace App\Models;

use App\Enums\RecurringStatus;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseRecurring extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id', 'expense_id', 'frequency', 'interval',
        'start_date', 'end_date', 'next_expense_date', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_expense_date' => 'date',
        'status' => RecurringStatus::class,
    ];

    public function expense(): BelongsTo { return $this->belongsTo(Expense::class); }
}
