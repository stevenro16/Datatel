<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderHistory extends Model
{
    protected $table = 'work_order_history';

    public $timestamps = false;

    protected $fillable = ['work_order_id', 'changed_by', 'field_name', 'old_value', 'new_value', 'comment', 'changed_at'];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function workOrder()  { return $this->belongsTo(WorkOrder::class); }
    public function changedBy()  { return $this->belongsTo(User::class, 'changed_by'); }
}
