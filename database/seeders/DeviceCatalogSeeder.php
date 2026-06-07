<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $devices = [
            // IP Phones
            ['label' => 'Cisco CP-7821 IP Phone',             'type' => 'Phone',        'keywords' => 'cisco 7821 voip desk sip'],
            ['label' => 'Cisco CP-7841 IP Phone',             'type' => 'Phone',        'keywords' => 'cisco 7841 voip desk sip'],
            ['label' => 'Cisco CP-7861 IP Phone',             'type' => 'Phone',        'keywords' => 'cisco 7861 voip desk sip'],
            ['label' => 'Cisco CP-8811 IP Phone',             'type' => 'Phone',        'keywords' => 'cisco 8811 voip desk sip'],
            ['label' => 'Cisco CP-8841 IP Phone',             'type' => 'Phone',        'keywords' => 'cisco 8841 voip desk sip'],
            ['label' => 'Cisco CP-8851 IP Phone',             'type' => 'Phone',        'keywords' => 'cisco 8851 voip desk sip'],
            ['label' => 'Cisco CP-8861 IP Phone',             'type' => 'Phone',        'keywords' => 'cisco 8861 voip desk sip'],
            ['label' => 'Cisco ATA 191 Analog Phone Adapter', 'type' => 'Phone',        'keywords' => 'cisco ata 191 analog telephone adapter fxs'],
            ['label' => 'Polycom VVX 300 IP Phone',           'type' => 'Phone',        'keywords' => 'polycom vvx300 voip desk sip'],
            ['label' => 'Polycom VVX 400 IP Phone',           'type' => 'Phone',        'keywords' => 'polycom vvx400 voip desk sip'],
            ['label' => 'Polycom VVX 500 IP Phone',           'type' => 'Phone',        'keywords' => 'polycom vvx500 voip desk sip color'],
            ['label' => 'Polycom VVX 600 IP Phone',           'type' => 'Phone',        'keywords' => 'polycom vvx600 voip desk sip color'],
            ['label' => 'Yealink T46S IP Phone',              'type' => 'Phone',        'keywords' => 'yealink t46s voip desk sip'],
            ['label' => 'Yealink T48S IP Phone',              'type' => 'Phone',        'keywords' => 'yealink t48s voip desk sip touchscreen'],
            ['label' => 'Yealink T54W IP Phone',              'type' => 'Phone',        'keywords' => 'yealink t54w voip wifi desk sip'],
            ['label' => 'Yealink T57W IP Phone',              'type' => 'Phone',        'keywords' => 'yealink t57w voip wifi touchscreen desk sip'],
            ['label' => 'Grandstream GXP2160 IP Phone',       'type' => 'Phone',        'keywords' => 'grandstream gxp2160 voip desk sip'],
            ['label' => 'Avaya J179 IP Phone',                'type' => 'Phone',        'keywords' => 'avaya j179 j100 voip desk sip'],
            // Routers
            ['label' => 'Cisco RV340 Dual WAN VPN Router',    'type' => 'Router',       'keywords' => 'cisco rv340 wan vpn router'],
            ['label' => 'Cisco ISR 1111 Router',              'type' => 'Router',       'keywords' => 'cisco isr 1111 1100 router'],
            ['label' => 'Cisco ISR 4321 Router',              'type' => 'Router',       'keywords' => 'cisco isr 4321 4000 router'],
            ['label' => 'Ubiquiti EdgeRouter X',              'type' => 'Router',       'keywords' => 'ubiquiti edgerouter x ubnt erx router'],
            ['label' => 'Ubiquiti EdgeRouter 4',              'type' => 'Router',       'keywords' => 'ubiquiti edgerouter 4 ubnt er4 router'],
            ['label' => 'MikroTik RB4011 Router',             'type' => 'Router',       'keywords' => 'mikrotik rb4011 routerboard router'],
            // Firewalls
            ['label' => 'Fortinet FortiGate 40F Firewall',    'type' => 'Firewall',     'keywords' => 'fortinet fortigate 40f utm router firewall'],
            ['label' => 'Fortinet FortiGate 60F Firewall',    'type' => 'Firewall',     'keywords' => 'fortinet fortigate 60f utm router firewall'],
            ['label' => 'Fortinet FortiGate 100F Firewall',   'type' => 'Firewall',     'keywords' => 'fortinet fortigate 100f utm router firewall'],
            ['label' => 'SonicWall TZ370 Firewall',           'type' => 'Firewall',     'keywords' => 'sonicwall tz370 utm firewall router'],
            ['label' => 'Palo Alto PA-220 Firewall',          'type' => 'Firewall',     'keywords' => 'palo alto pa220 pa-220 firewall ngfw'],
            // Switches
            ['label' => 'Cisco SG350-28 28-Port Managed Switch',   'type' => 'Switch', 'keywords' => 'cisco sg350 28 port managed gigabit switch'],
            ['label' => 'Cisco SG350-52 52-Port Managed Switch',   'type' => 'Switch', 'keywords' => 'cisco sg350 52 port managed gigabit switch'],
            ['label' => 'Cisco CBS350-24T-4G 24-Port Switch',      'type' => 'Switch', 'keywords' => 'cisco cbs350 24t 4g switch managed'],
            ['label' => 'Cisco CBS350-48T-4G 48-Port Switch',      'type' => 'Switch', 'keywords' => 'cisco cbs350 48t 4g switch managed'],
            ['label' => 'Cisco Catalyst 2960-X 24-Port Switch',    'type' => 'Switch', 'keywords' => 'cisco catalyst 2960x 24 port switch'],
            ['label' => 'Cisco Catalyst 9200L 24-Port Switch',     'type' => 'Switch', 'keywords' => 'cisco catalyst 9200l 24 port switch'],
            ['label' => 'Ubiquiti UniFi USW-24-POE Switch',        'type' => 'Switch', 'keywords' => 'ubiquiti unifi usw 24 poe switch'],
            ['label' => 'Ubiquiti UniFi USW-48-POE Switch',        'type' => 'Switch', 'keywords' => 'ubiquiti unifi usw 48 poe switch'],
            ['label' => 'Ubiquiti UniFi USW-Pro-24-POE Switch',    'type' => 'Switch', 'keywords' => 'ubiquiti unifi usw pro 24 poe switch'],
            ['label' => 'HP Aruba 1930 24G Switch',                'type' => 'Switch', 'keywords' => 'hp aruba 1930 24g jl682a switch'],
            ['label' => 'HP Aruba 2530-24G Switch',                'type' => 'Switch', 'keywords' => 'hp aruba 2530 24g j9776a switch'],
            ['label' => 'Netgear GS724T Smart Switch',             'type' => 'Switch', 'keywords' => 'netgear gs724t 24 port smart switch'],
            ['label' => 'Netgear MS510TX Multi-Gig Switch',        'type' => 'Switch', 'keywords' => 'netgear ms510tx multi gig 10g switch'],
            // Access Points
            ['label' => 'Ubiquiti UniFi U6-Pro Wi-Fi 6 AP',        'type' => 'Access Point', 'keywords' => 'ubiquiti unifi u6 pro wifi6 802.11ax ap'],
            ['label' => 'Ubiquiti UniFi U6-LR Wi-Fi 6 AP',         'type' => 'Access Point', 'keywords' => 'ubiquiti unifi u6 lr long range wifi6 ap'],
            ['label' => 'Ubiquiti UniFi U6-Lite Wi-Fi 6 AP',       'type' => 'Access Point', 'keywords' => 'ubiquiti unifi u6 lite wifi6 ap'],
            ['label' => 'Ubiquiti UniFi AP-AC-Pro Access Point',    'type' => 'Access Point', 'keywords' => 'ubiquiti unifi ap ac pro wifi5 802.11ac ap'],
            ['label' => 'Ubiquiti UniFi AP-AC-Lite Access Point',   'type' => 'Access Point', 'keywords' => 'ubiquiti unifi ap ac lite wifi5 ap'],
            ['label' => 'Cisco Catalyst 9120AX Access Point',       'type' => 'Access Point', 'keywords' => 'cisco catalyst 9120 wifi6 802.11ax ap'],
            ['label' => 'Cisco Aironet 2802i Access Point',         'type' => 'Access Point', 'keywords' => 'cisco aironet 2802 wifi5 802.11ac ap'],
            ['label' => 'Aruba AP-515 Access Point',                'type' => 'Access Point', 'keywords' => 'aruba ap515 wifi6 802.11ax ap'],
            ['label' => 'Ruckus R650 Wi-Fi 6 Access Point',         'type' => 'Access Point', 'keywords' => 'ruckus r650 wifi6 802.11ax ap'],
            // Modems
            ['label' => 'Arris SURFboard SB8200 Cable Modem',  'type' => 'Modem', 'keywords' => 'arris surfboard sb8200 docsis 3.1 cable modem'],
            ['label' => 'Arris SURFboard SBG10 Modem/Router',  'type' => 'Modem', 'keywords' => 'arris surfboard sbg10 docsis cable modem router'],
            ['label' => 'Motorola MB8600 Cable Modem',          'type' => 'Modem', 'keywords' => 'motorola mb8600 docsis 3.1 cable modem'],
            ['label' => 'Netgear CM1000 Cable Modem',           'type' => 'Modem', 'keywords' => 'netgear cm1000 docsis 3.1 cable modem'],
            // Cable
            ['label' => 'Cat5e UTP Cable (Bulk, 1000ft)',             'type' => 'Cable', 'keywords' => 'cat5e utp bulk ethernet cable 1000ft spool'],
            ['label' => 'Cat6 UTP Cable (Bulk, 1000ft)',              'type' => 'Cable', 'keywords' => 'cat6 utp bulk ethernet cable 1000ft spool'],
            ['label' => 'Cat6A UTP Cable (Bulk, 1000ft)',             'type' => 'Cable', 'keywords' => 'cat6a utp bulk ethernet cable 1000ft spool 10g'],
            ['label' => 'Cat6A STP Shielded Cable (Bulk, 1000ft)',    'type' => 'Cable', 'keywords' => 'cat6a stp shielded bulk ethernet cable 1000ft 10g'],
            ['label' => 'Cat7 SSTP Cable (Bulk, 1000ft)',             'type' => 'Cable', 'keywords' => 'cat7 sstp shielded bulk ethernet cable 1000ft 10g'],
            ['label' => 'OS2 Single-Mode Fiber Cable (1000ft)',       'type' => 'Cable', 'keywords' => 'os2 smf single mode fiber optic cable 1000ft spool'],
            ['label' => 'OM3 Multi-Mode Fiber Cable (1000ft)',        'type' => 'Cable', 'keywords' => 'om3 mmf multi mode fiber optic cable 1000ft spool'],
            ['label' => 'OM4 Multi-Mode Fiber Cable (1000ft)',        'type' => 'Cable', 'keywords' => 'om4 mmf multi mode fiber optic cable 1000ft spool'],
            ['label' => 'RG6 Coaxial Cable (Bulk, 1000ft)',          'type' => 'Cable', 'keywords' => 'rg6 coax coaxial cable 1000ft spool'],
            ['label' => 'RG59 Coaxial Cable (Bulk, 1000ft)',         'type' => 'Cable', 'keywords' => 'rg59 coax coaxial cable 1000ft spool'],
            // Connectors
            ['label' => 'RJ45 Cat5e Connectors (100-pack)',           'type' => 'Connector', 'keywords' => 'rj45 cat5e crimp connector modular plug'],
            ['label' => 'RJ45 Cat6 Connectors (100-pack)',            'type' => 'Connector', 'keywords' => 'rj45 cat6 crimp connector modular plug'],
            ['label' => 'RJ45 Keystone Jack Cat5e',                   'type' => 'Connector', 'keywords' => 'rj45 keystone jack cat5e 110 punch down'],
            ['label' => 'RJ45 Keystone Jack Cat6',                    'type' => 'Connector', 'keywords' => 'rj45 keystone jack cat6 110 punch down'],
            ['label' => 'Leviton GigaMax Cat6 Keystone Jack',         'type' => 'Connector', 'keywords' => 'leviton gigamax cat6 keystone jack 5G108'],
            ['label' => 'Panduit Mini-Com Cat6A Keystone Jack',       'type' => 'Connector', 'keywords' => 'panduit mini-com cat6a keystone jack 10g'],
            ['label' => 'LC Fiber Duplex Connector',                  'type' => 'Connector', 'keywords' => 'lc fiber optic duplex connector sm mm'],
            ['label' => 'SC Fiber Duplex Connector',                  'type' => 'Connector', 'keywords' => 'sc fiber optic duplex connector sm mm'],
            ['label' => 'ST Fiber Connector',                         'type' => 'Connector', 'keywords' => 'st fiber optic connector sm mm'],
            ['label' => 'F-Type Coax Connector (Crimp)',              'type' => 'Connector', 'keywords' => 'f type coax coaxial crimp connector rg6 rg59'],
            ['label' => 'BNC Coax Connector (Crimp)',                 'type' => 'Connector', 'keywords' => 'bnc coax coaxial crimp connector rg6 rg59'],
            // Patch Panels
            ['label' => '24-Port Cat5e Patch Panel (1U)',             'type' => 'Patch Panel', 'keywords' => 'cat5e 24 port patch panel 1u 110 rackmount'],
            ['label' => '24-Port Cat6 Patch Panel (1U)',              'type' => 'Patch Panel', 'keywords' => 'cat6 24 port patch panel 1u 110 rackmount'],
            ['label' => '48-Port Cat6 Patch Panel (2U)',              'type' => 'Patch Panel', 'keywords' => 'cat6 48 port patch panel 2u 110 rackmount'],
            ['label' => '24-Port Cat6A Patch Panel (1U)',             'type' => 'Patch Panel', 'keywords' => 'cat6a 24 port patch panel 1u 10g rackmount'],
            ['label' => '24-Port LC Fiber Patch Panel (1U)',          'type' => 'Patch Panel', 'keywords' => 'fiber lc 24 port patch panel 1u rackmount'],
            ['label' => 'Leviton 24-Port Cat6 Patch Panel',          'type' => 'Patch Panel', 'keywords' => 'leviton 24 port cat6 patch panel 5G702'],
            ['label' => 'Panduit 24-Port Cat6A Patch Panel',         'type' => 'Patch Panel', 'keywords' => 'panduit 24 port cat6a patch panel dp24688'],
            // Security Cameras
            ['label' => 'Axis P3245-V Dome Network Camera',          'type' => 'Camera', 'keywords' => 'axis p3245 dome ip poe camera 1080p'],
            ['label' => 'Axis M3106-L Mk II Mini Dome Camera',       'type' => 'Camera', 'keywords' => 'axis m3106 mini dome ip poe camera 4mp'],
            ['label' => 'Hikvision DS-2CD2143G2-I Dome Camera',      'type' => 'Camera', 'keywords' => 'hikvision ds2cd2143 dome ip poe camera 4mp'],
            ['label' => 'Hikvision DS-2CD2347G2-LU ColorVu Camera',  'type' => 'Camera', 'keywords' => 'hikvision ds2cd2347 colorvu ip poe camera 4mp'],
            ['label' => 'Dahua IPC-HDW2831T-AS Dome Camera',         'type' => 'Camera', 'keywords' => 'dahua ipc hdw2831 dome ip poe camera 8mp 4k'],
            ['label' => 'Ubiquiti UniFi G4 Bullet Camera',           'type' => 'Camera', 'keywords' => 'ubiquiti unifi protect g4 bullet uvc camera'],
            ['label' => 'Ubiquiti UniFi G4 Dome Camera',             'type' => 'Camera', 'keywords' => 'ubiquiti unifi protect g4 dome uvc camera'],
            ['label' => 'Ubiquiti UniFi G4 Pro Camera',              'type' => 'Camera', 'keywords' => 'ubiquiti unifi protect g4 pro uvc camera 8mp'],
            ['label' => 'Reolink RLC-810A 4K PoE Camera',            'type' => 'Camera', 'keywords' => 'reolink rlc-810a 4k 8mp poe ip camera'],
            // NVR
            ['label' => 'Hikvision DS-7608NI-K2 8-Channel NVR',     'type' => 'NVR', 'keywords' => 'hikvision 7608 8 channel nvr network video recorder'],
            ['label' => 'Hikvision DS-7616NI-K2 16-Channel NVR',    'type' => 'NVR', 'keywords' => 'hikvision 7616 16 channel nvr network video recorder'],
            ['label' => 'Dahua NVR4108HS-8P 8-Channel NVR',         'type' => 'NVR', 'keywords' => 'dahua nvr 8 channel poe network video recorder'],
            ['label' => 'Ubiquiti UniFi Protect NVR',               'type' => 'NVR', 'keywords' => 'ubiquiti unifi protect nvr unvr4'],
            // UPS
            ['label' => 'APC Smart-UPS 750VA UPS',                  'type' => 'UPS', 'keywords' => 'apc smart ups 750va battery backup uninterruptible'],
            ['label' => 'APC Smart-UPS 1500VA UPS',                 'type' => 'UPS', 'keywords' => 'apc smart ups 1500va battery backup uninterruptible'],
            ['label' => 'CyberPower CP1500AVRLCD UPS',              'type' => 'UPS', 'keywords' => 'cyberpower 1500va lcd battery backup ups'],
            // Rack / Infrastructure
            ['label' => '12U Wall Mount Network Cabinet',            'type' => 'Rack', 'keywords' => '12u wall mount rack cabinet network enclosure'],
            ['label' => '24U Open Frame Server Rack',                'type' => 'Rack', 'keywords' => '24u open frame server rack 4 post'],
            ['label' => '42U Server Rack Cabinet',                   'type' => 'Rack', 'keywords' => '42u server rack cabinet enclosure data center'],
            ['label' => '1U Horizontal Cable Manager',               'type' => 'Rack', 'keywords' => '1u horizontal cable manager rackmount'],
            ['label' => '2U Vertical Cable Manager',                 'type' => 'Rack', 'keywords' => '2u vertical cable manager rackmount'],
            ['label' => '1U Blank Rack Panel (10-pack)',             'type' => 'Rack', 'keywords' => '1u blank panel filler rackmount'],
        ];

        $rows = [];
        foreach ($devices as $i => $d) {
            $rows[] = array_merge($d, [
                'is_active'  => 1,
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('device_catalog')->insertOrIgnore($rows);
    }
}
