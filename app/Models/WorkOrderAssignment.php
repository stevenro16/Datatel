<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderAssignment extends Model
{
    protected $fillable = ['work_order_id', 'user_id', 'assigned_by'];
    protected $touches  = ['workOrder'];

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function employee()  { return $this->belongsTo(User::class, 'user_id'); }
}
