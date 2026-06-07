<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    const STATUS_NEW         = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_CLOSED      = 'closed';

    protected $fillable = [
        'name', 'email', 'phone', 'company', 'services', 'message', 'status',
    ];

    protected $casts = [
        'services' => 'array',
    ];

    public function notes()
    {
        return $this->hasMany(InquiryNote::class)->latest();
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            self::STATUS_NEW         => 'New',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_CLOSED      => 'Closed',
            default                  => ucfirst($this->status),
        };
    }

    public function statusClass(): string
    {
        return match($this->status) {
            self::STATUS_NEW         => 'badge-blue',
            self::STATUS_IN_PROGRESS => 'badge-yellow',
            self::STATUS_CLOSED      => 'badge-gray',
            default                  => 'badge-gray',
        };
    }
}
