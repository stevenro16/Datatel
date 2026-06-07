<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    protected $fillable = ['name', 'icon', 'description', 'image', 'default_unit_price', 'is_active', 'sort_order'];

    protected $casts = ['default_unit_price' => 'decimal:2'];

    public function imageUrl(): ?string
    {
        return $this->image ? asset('images/services/' . $this->image) : null;
    }

    public function workOrders()
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_services');
    }

    public static function iconSet(): array
    {
        return [
            'cable'      => ['label' => 'Cable / Wiring',          'paths' => '<line x1="3" y1="6" x2="10" y2="6"/><line x1="14" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="10" y2="18"/><line x1="14" y1="18" x2="21" y2="18"/><line x1="10" y1="3" x2="10" y2="21"/><line x1="14" y1="3" x2="14" y2="21"/>'],
            'zap'        => ['label' => 'Fiber / Light Beam',      'paths' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>'],
            'network'    => ['label' => 'Network Nodes',           'paths' => '<rect x="2" y="2" width="5" height="5" rx="1"/><rect x="17" y="2" width="5" height="5" rx="1"/><rect x="9" y="17" width="6" height="5" rx="1"/><path d="M4.5 7v3a1 1 0 001 1h13a1 1 0 001-1V7"/><line x1="12" y1="11" x2="12" y2="17"/>'],
            'shield'     => ['label' => 'Security / Shield',       'paths' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>'],
            'monitor'    => ['label' => 'Display / Monitor',       'paths' => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>'],
            'wifi'       => ['label' => 'Wireless / WiFi',         'paths' => '<path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>'],
            'phone'      => ['label' => 'Telephone / VoIP',        'paths' => '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 014.15 13a19.79 19.79 0 01-3.07-8.67A2 2 0 013.12 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>'],
            'server'     => ['label' => 'Server / Data Center',    'paths' => '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>'],
            'clipboard'  => ['label' => 'Testing / Certification', 'paths' => '<path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12l2 2 4-4"/>'],
            'users'      => ['label' => 'Consulting / Team',       'paths' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>'],
            'tool'       => ['label' => 'Installation / Repair',   'paths' => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>'],
            'radio'      => ['label' => 'Radio / Antenna',         'paths' => '<path d="M4.9 4.9a10 10 0 0114.14 0"/><path d="M7.05 7.05A7 7 0 0116.95 7"/><path d="M9.17 9.17a4 4 0 015.66 0"/><line x1="12" y1="12" x2="12.01" y2="12"/>'],
            'globe'      => ['label' => 'Internet / WAN',          'paths' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>'],
            'camera'     => ['label' => 'Camera / CCTV',           'paths' => '<path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/>'],
            'database'   => ['label' => 'Database / Storage',      'paths' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>'],
            'cpu'        => ['label' => 'Computing / IT',          'paths' => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/>'],
            'lock'       => ['label' => 'Access Control',          'paths' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>'],
            'video'      => ['label' => 'Video / AV System',       'paths' => '<polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2"/>'],
            'headphones' => ['label' => 'Audio / Sound',           'paths' => '<path d="M3 18v-6a9 9 0 0118 0v6"/><path d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3z"/><path d="M3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>'],
            'plug'       => ['label' => 'Power / Electrical',      'paths' => '<path d="M12 22v-5"/><path d="M9 7V2"/><path d="M15 7V2"/><path d="M18 7H6l1.5 7.5a4.5 4.5 0 009 0L18 7z"/>'],
            'box'        => ['label' => 'Equipment / Hardware',    'paths' => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
        ];
    }

    public function svgIcon(int $size = 18): string
    {
        $set = static::iconSet();
        $key = ($this->icon && isset($set[$this->icon])) ? $this->icon : null;
        if (!$key) return '';
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"'
             . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"'
             . ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
             . $set[$key]['paths']
             . '</svg>';
    }
}
