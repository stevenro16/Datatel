<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'owner_name', 'tax_id', 'phone', 'email', 'website',
        'address_street', 'address_city', 'address_state', 'address_zip',
        'tax_rate', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return ['tax_rate' => 'decimal:6'];
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'company_members')
            ->withPivot('status', 'is_primary', 'approved_by', 'approved_at')
            ->withTimestamps();
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function sites()
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
