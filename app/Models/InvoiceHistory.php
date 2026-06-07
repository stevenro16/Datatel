<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceHistory extends Model
{
    protected $table = 'invoice_history';
    public $timestamps = false;

    protected $fillable = [
        'invoice_id', 'changed_by', 'field_name',
        'old_value', 'new_value', 'comment', 'changed_at',
    ];

    protected function casts(): array
    {
        return ['changed_at' => 'datetime'];
    }

    public function invoice()   { return $this->belongsTo(Invoice::class); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by'); }
}
