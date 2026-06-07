<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    const ROLE_CUSTOMER = 'customer';
    const ROLE_EMPLOYEE = 'employee';
    const ROLE_ADMIN    = 'admin';

    const STATUS_PENDING  = 'pending';
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name', 'title', 'email', 'password', 'role', 'status',
        'phone', 'preferred_availability', 'profile_photo', 'is_super_admin', 'last_login_at',
        'requested_company_id', 'requested_company_name',
        'home_street', 'home_city', 'home_state', 'home_zip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'last_login_at'          => 'datetime',
            'password'               => 'hashed',
            'is_super_admin'         => 'boolean',
            'preferred_availability' => 'array',
        ];
    }

    public function isAdmin(): bool    { return $this->role === self::ROLE_ADMIN; }
    public function isEmployee(): bool { return $this->role === self::ROLE_EMPLOYEE; }
    public function isCustomer(): bool { return $this->role === self::ROLE_CUSTOMER; }
    public function isSuperAdmin(): bool { return $this->is_super_admin && $this->isAdmin(); }
    public function isPending(): bool  { return $this->status === self::STATUS_PENDING; }
    public function isApproved(): bool { return $this->status === self::STATUS_ACTIVE; }

    public function requestedCompany()
    {
        return $this->belongsTo(Company::class, 'requested_company_id');
    }

    public function companyMemberships()
    {
        return $this->hasMany(CompanyMember::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_members')
            ->withPivot('status', 'is_primary')
            ->withTimestamps();
    }

    public function savedAddresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'customer_id');
    }

    public function assignedWorkOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_assignments')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }
}
