<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderVisitTech extends Model
{
    protected $table = 'work_order_visit_techs';

    protected $fillable = ['visit_id', 'user_id', 'assigned_by'];

    public function visit()      { return $this->belongsTo(WorkOrderVisit::class, 'visit_id'); }
    public function user()       { return $this->belongsTo(User::class, 'user_id'); }
    public function assigner()   { return $this->belongsTo(User::class, 'assigned_by'); }
}
