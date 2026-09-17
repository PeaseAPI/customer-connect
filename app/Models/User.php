<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Traits\HasCustomFields;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasCustomFields, HasFiles;

    protected $fillable = [
        'company_id', 'name', 'email', 'mobile', 'password', 'image',
        'gender', 'locale', 'status', 'login', 'last_login',
        'email_notifications', 'country_id', 'two_factor_enabled',
        'two_factor_secret', 'wechat_openid', 'wechat_unionid',
        'dingtalk_userid', 'wework_userid', 'feishu_userid',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_login' => 'datetime',
        'email_notifications' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'gender' => Gender::class,
        'status' => UserStatus::class,
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function employeeDetail(): HasOne { return $this->hasOne(EmployeeDetail::class); }
    public function clientDetail(): HasOne { return $this->hasOne(ClientDetail::class); }
    public function userAuths(): HasMany { return $this->hasMany(UserAuth::class); }
    public function departments(): BelongsToMany { return $this->belongsToMany(Department::class, 'department_users'); }
    public function designation(): BelongsTo { return $this->belongsTo(Designation::class); }
    public function country(): BelongsTo { return $this->belongsTo(Country::class); }
        public function assignedTasks(): HasMany { return $this->hasMany(Task::class, 'assign_to'); }
    public function attendances(): HasMany { return $this->hasMany(Attendance::class); }
    public function leaves(): HasMany { return $this->hasMany(Leave::class); }
    public function timelogs(): HasMany { return $this->hasMany(Timelog::class); }
    public function notifications(): HasMany { return $this->hasMany(Notification::class); }
    public function emergencyContacts(): HasMany { return $this->hasMany(EmergencyContact::class); }
    public function visas(): HasMany { return $this->hasMany(EmployeeVisa::class); }
    public function promotions(): HasMany { return $this->hasMany(Promotion::class); }
    public function clientContacts(): HasMany { return $this->hasMany(ClientContact::class, 'client_id'); }
    public function clientNotes(): HasMany { return $this->hasMany(ClientNote::class, 'client_id'); }
    public function clientDocuments(): HasMany { return $this->hasMany(ClientDocument::class, 'client_id'); }
    public function stickyNotes(): HasMany { return $this->hasMany(StickyNote::class); }

    public function isSuperAdmin(): bool { return $this->hasRole('super-admin'); }
    public function isAdmin(): bool { return $this->hasRole('admin'); }
    public function isClient(): bool { return $this->hasRole('client'); }
    public function isEmployee(): bool { return $this->hasRole('employee'); }

    public function scopeActive($query) { return $query->where('status', UserStatus::Active); }
    public function scopeEmployees($query) { return $query->whereHas('roles', fn($q) => $q->where('name', 'employee')); }
    public function scopeClients($query) { return $query->whereHas('roles', fn($q) => $q->where('name', 'client')); }
}