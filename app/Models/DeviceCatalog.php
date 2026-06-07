<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCatalog extends Model
{
    protected $table = 'device_catalog';

    protected $fillable = ['label', 'type', 'keywords', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];
}
