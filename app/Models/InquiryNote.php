<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InquiryNote extends Model
{
    protected $fillable = ['inquiry_id', 'admin_id', 'note'];

    public function inquiry() { return $this->belongsTo(Inquiry::class); }
    public function admin()   { return $this->belongsTo(User::class, 'admin_id'); }
}
