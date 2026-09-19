<?php

namespace App\Models;

use App\Enums\CompanyStatus;
use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'logo',
        'logo_background_color',
        'login_background',
        'subdomain',
        'custom_domain',
        'status',
        'package_id',
        'license_type',
        'license_expire_on',
        'app_id',
        'app_secret',
    ];

    protected $casts = [
        'status' => CompanyStatus::class,
        'license_expire_on' => 'date',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function organisationSetting(): HasOne
    {
        return $this->hasOne(OrganisationSetting::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', CompanyStatus::Active);
    }
}
