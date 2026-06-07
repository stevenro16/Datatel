<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderVisitSignature extends Model
{
    public $timestamps = false;

    protected $table = 'work_order_visit_signatures';

    protected $fillable = [
        'visit_id', 'work_order_id', 'signer_name', 'signature_path',
        'collected_by', 'ip_address', 'signed_at',
    ];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime'];
    }

    public function visit()       { return $this->belongsTo(WorkOrderVisit::class, 'visit_id'); }
    public function workOrder()   { return $this->belongsTo(WorkOrder::class); }
    public function collectedBy() { return $this->belongsTo(User::class, 'collected_by'); }
}
