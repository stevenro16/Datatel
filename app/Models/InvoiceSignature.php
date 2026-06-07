<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSignature extends Model
{
    public $timestamps = false;

    protected $fillable = ['invoice_id', 'user_id', 'typed_name', 'signature_path', 'ip_address', 'signed_at'];

    protected function casts(): array
    {
        return ['signed_at' => 'datetime'];
    }

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
