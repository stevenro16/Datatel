<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceLineItem extends Model
{
    protected $fillable = ['invoice_id', 'description', 'quantity', 'unit', 'unit_price', 'sort_order'];

    public function invoice() { return $this->belongsTo(Invoice::class); }
}
