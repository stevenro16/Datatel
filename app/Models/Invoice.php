<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT           = 'draft';
    const STATUS_ISSUED          = 'issued';
    const STATUS_PAYMENT_RECEIVED = 'payment_received';
    const STATUS_COMPLETED       = 'completed';
    const STATUS_CANCELED        = 'canceled';

    protected $fillable = [
        'work_order_id', 'covered_visit_ids', 'created_by', 'status', 'cancel_reason',
        'subtotal', 'tax_rate', 'tax_amount', 'discount', 'total',
        'payment_terms', 'footer_note', 'due_date', 'pdf_path', 'transaction_reference',
    ];

    protected function casts(): array
    {
        return [
            'due_date'          => 'date',
            'covered_visit_ids' => 'array',
            'subtotal'          => 'decimal:2',
            'tax_rate'          => 'decimal:4',
            'tax_amount'        => 'decimal:2',
            'discount'          => 'decimal:2',
            'total'             => 'decimal:2',
        ];
    }

    public function workOrder()  { return $this->belongsTo(WorkOrder::class); }
    public function lineItems()  { return $this->hasMany(InvoiceLineItem::class); }
    public function signature()  { return $this->hasOne(InvoiceSignature::class); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }
    public function history()    { return $this->hasMany(InvoiceHistory::class)->orderBy('changed_at', 'desc'); }
}
