<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasCompanyScope, HasFactory;

    protected $fillable = ['company_id', 'type_name', 'is_paid', 'paid_leaves', 'carry_forward'];

    protected $casts = ['is_paid' => 'boolean', 'paid_leaves' => 'integer', 'carry_forward' => 'boolean'];
}
