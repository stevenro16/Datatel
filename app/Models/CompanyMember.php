<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMember extends Model
{
    protected $fillable = ['company_id', 'user_id', 'status', 'is_primary', 'approved_by', 'approved_at'];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime', 'is_primary' => 'boolean'];
    }

    public function company()    { return $this->belongsTo(Company::class); }
    public function user()       { return $this->belongsTo(User::class); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
}
