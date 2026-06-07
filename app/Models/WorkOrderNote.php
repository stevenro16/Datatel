<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderNote extends Model
{
    protected $fillable = ['work_order_id', 'user_id', 'body', 'visibility', 'attachment_path', 'attachment_name'];
    protected $touches  = ['workOrder'];

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function user()      { return $this->belongsTo(User::class); }
    public function author()    { return $this->belongsTo(User::class, 'user_id'); }

    public function getIsInternalAttribute(): bool
    {
        return $this->visibility === 'internal';
    }
}
