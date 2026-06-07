<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderAttachment extends Model
{
    protected $fillable = ['work_order_id', 'uploaded_by', 'original_name', 'stored_name', 'mime_type', 'size_bytes'];
    protected $touches  = ['workOrder'];

    public function workOrder()   { return $this->belongsTo(WorkOrder::class); }
    public function uploadedBy()  { return $this->belongsTo(User::class, 'uploaded_by'); }
}
