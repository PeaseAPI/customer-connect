<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = [
        'company_id',
        'category_name',
        'description',
    ];
}
