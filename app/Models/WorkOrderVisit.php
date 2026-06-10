<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderVisit extends Model
{
    use HasFactory;

    const CONFIRMATION_PENDING   = 'pending';
    const CONFIRMATION_CONFIRMED = 'confirmed';
    const CONFIRMATION_DECLINED  = 'declined';

    protected $fillable = [
        'work_order_id',
        'scheduled_at',
        'duration_estimate_minutes',
        'notes',
        'created_by',
        'confirmation_status',
        'confirmed_by',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function workOrder()   { return $this->belongsTo(WorkOrder::class); }
    public function creator()     { return $this->belongsTo(User::class, 'created_by'); }
    public function confirmedBy() { return $this->belongsTo(User::class, 'confirmed_by'); }

    public function techs()       { return $this->hasMany(WorkOrderVisitTech::class, 'visit_id'); }
    public function techUsers()   { return $this->belongsToMany(User::class, 'work_order_visit_techs', 'visit_id', 'user_id')->withPivot('assigned_by')->withTimestamps(); }
    public function timeEntries() { return $this->hasMany(TimeEntry::class, 'visit_id'); }
    public function signature()   { return $this->hasOne(WorkOrderVisitSignature::class, 'visit_id'); }
}
