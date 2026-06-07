<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Structured Cabling',    'icon' => 'cable',     'description' => 'Cat5e/6/6A installations, patch panels, cable management', 'sort_order' => 1],
            ['name' => 'Fiber Optic',           'icon' => 'zap',       'description' => 'Single-mode, multi-mode, splicing, OTDR testing', 'sort_order' => 2],
            ['name' => 'Network Infrastructure','icon' => 'network',   'description' => 'Switches, routers, racks, UPS, cable trays', 'sort_order' => 3],
            ['name' => 'Security Systems',      'icon' => 'shield',    'description' => 'IP cameras, access control, intercom systems', 'sort_order' => 4],
            ['name' => 'Audio/Visual',          'icon' => 'video',     'description' => 'Conference AV, displays, projection, speakers', 'sort_order' => 5],
            ['name' => 'Wireless',              'icon' => 'wifi',      'description' => 'Wi-Fi access points, site surveys, wireless controllers', 'sort_order' => 6],
            ['name' => 'Telephone / VoIP',      'icon' => 'phone',     'description' => 'Analog/digital wiring, VoIP deployment, PBX', 'sort_order' => 7],
            ['name' => 'Data Center',           'icon' => 'server',    'description' => 'Rack & stack, power distribution, cable labeling', 'sort_order' => 8],
            ['name' => 'Testing & Certification','icon' => 'clipboard', 'description' => 'Fluke cable testing, certification reports', 'sort_order' => 9],
            ['name' => 'Consulting',            'icon' => 'users',     'description' => 'Site surveys, network assessments, project planning', 'sort_order' => 10],
        ];

        foreach ($services as $service) {
            DB::table('service_types')->insertOrIgnore(array_merge($service, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
