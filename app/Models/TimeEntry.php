<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    protected $fillable = [
        'user_id', 'work_order_id', 'visit_id', 'clocked_in_at', 'clocked_out_at',
        'gps_lat', 'gps_lng', 'note',
    ];

    protected function casts(): array
    {
        return [
            'clocked_in_at'  => 'datetime',
            'clocked_out_at' => 'datetime',
        ];
    }

    public function user()      { return $this->belongsTo(User::class); }
    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function visit()     { return $this->belongsTo(WorkOrderVisit::class, 'visit_id'); }
    public function correction(){ return $this->hasOne(TimeCorrection::class); }

    public function totalMinutes(): ?int
    {
        if (!$this->clocked_out_at) return null;
        return (int) $this->clocked_in_at->diffInMinutes($this->clocked_out_at);
    }
}
