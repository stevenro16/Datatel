<?php

namespace Database\Seeders;

use App\Models\AdminSetting;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderHistory;
use App\Models\WorkOrderVisit;
use App\Models\WorkOrderVisitTech;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkOrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $employees = User::where('role', 'employee')->get();
        $services  = ServiceType::where('is_active', true)->get();
        $admin     = User::where('role', 'admin')->first();

        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Run AdminUserSeeder first.');
            return;
        }

        $taxRate      = (float) AdminSetting::get('default_tax_rate', 0.075);
        $paymentTerms = AdminSetting::get('invoice_terms', 'Net 30');
        $footerNote   = AdminSetting::get('invoice_footer', 'Thank you for your business.');
        $dueDays      = (int) AdminSetting::get('invoice_due_days', 30);

        $statuses = [
            WorkOrder::STATUS_NEW,
            WorkOrder::STATUS_TRIAGED,
            WorkOrder::STATUS_SCHEDULED,
            WorkOrder::STATUS_AWAITING_FEEDBACK,
            WorkOrder::STATUS_SERVICES_PERFORMED,
            WorkOrder::STATUS_INVOICE_PREPARED,
            WorkOrder::STATUS_BILLED,
            WorkOrder::STATUS_COMPLETED,
        ];

        $urgencies = [
            WorkOrder::URGENCY_ROUTINE,
            WorkOrder::URGENCY_ROUTINE,
            WorkOrder::URGENCY_ROUTINE,
            WorkOrder::URGENCY_URGENT,
            WorkOrder::URGENCY_EMERGENCY,
        ];

        $descriptions = [
            'Install structured cabling for new office buildout — 24 drops across two floors.',
            'Fiber optic backbone between server room and IDF closet needs replacement after damage.',
            'Add 8 new network drops to the warehouse floor for handheld scanner stations.',
            'VoIP system upgrade — replace analog lines with SIP trunking and new desk phones.',
            'Security camera cabling — 6 outdoor IP cameras to existing NVR system.',
            'Troubleshoot intermittent connectivity issues on third-floor switch uplink.',
            'Run CAT6 to new conference room AV equipment and wireless access point.',
            'Install patch panels and label all existing runs in server room.',
            'Replace damaged conduit and re-pull 12 drops in production area.',
            'Extend existing WiFi coverage to new breakroom addition.',
            'Test and certify all cabling after renovation — approx 40 drops.',
            'Install two new 48-port switches and configure VLANs.',
            'Route fiber between two buildings for dedicated point-to-point link.',
            'Cable cleanup and documentation — existing infrastructure is unlabeled.',
            'Install UPS units in server room and connect to monitoring software.',
            'Set up new reception desk — 4 data drops, 2 phone lines.',
            'Investigate and repair intermittent phone system outages reported by staff.',
            'Add network drops to new manager offices on second floor — 6 drops.',
            'Install ceiling-mounted WAPs in open office — 4 units with POE.',
            'Perform annual network infrastructure inspection and certification.',
        ];

        $equipmentDetails = [
            'Dell PowerConnect switches, CAT6 plenum cable, Leviton patch panels.',
            'Corning OM4 fiber, SC connectors, existing splice enclosures.',
            'CAT6A cable, Panduit jacks, surface mount boxes.',
            'Cisco CUCM, Polycom desk phones, existing SIP infrastructure.',
            'Hikvision IP cameras, CAT6 cable, existing NVR system.',
            'Cisco Catalyst 2960 switches, CAT6 horizontal runs.',
            'CAT6 cable, HDMI over CAT6 extenders, Ubiquiti WAP.',
            'Leviton patch panels, CAT6 cable, cable management trays.',
            'EMT conduit, CAT6 plenum, existing junction boxes.',
            'Ubiquiti UniFi APs, existing controller, CAT6 drops.',
            'Fluke DSX cable analyzer, certification reports required.',
            'Cisco Catalyst 2960X, existing fiber uplinks.',
            'Corning OS2 single-mode fiber, LC connectors, media converters.',
            null,
            'APC Smart-UPS 1500, network management cards.',
            'CAT6, keystone jacks, faceplate covers, patch cables.',
            'NEC SL2100 PBX, existing analog handsets.',
            'CAT6 cable, Leviton jacks, surface mount boxes.',
            'Ubiquiti UniFi U6-Pro, POE injectors, CAT6 drops.',
            null,
        ];

        $streets = [
            '1250 Industrial Blvd', '430 Commerce Dr', '8800 Enterprise Way',
            '2100 Tech Park Dr', '550 Main St Ste 200', '3300 Harbor Blvd',
            '175 Business Center Dr', '6600 Airport Rd', '900 Oak Ave',
            '4400 Riverside Dr',
        ];

        $contactNames = [
            'Mike Johnson', 'Sarah Williams', 'David Chen', 'Lisa Martinez',
            'Tom Anderson', 'Jennifer Lee', 'Robert Davis', 'Amanda Wilson',
            'James Taylor', 'Karen Brown',
        ];

        $contactPhones = [
            '(909) 555-0101', '(909) 555-0182', '(909) 555-0143',
            '(909) 555-0167', '(909) 555-0129', '(909) 555-0154',
            '(909) 555-0138', '(909) 555-0176', '(909) 555-0112',
            '(909) 555-0195',
        ];

        // Build list of Mon-Fri business hour slots over the next 2 weeks
        $slots = [];
        $startTimes = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00'];
        $today = Carbon::today();
        for ($i = 1; $i <= 14; $i++) {
            $day = $today->copy()->addDays($i);
            if ($day->isWeekend()) {
                continue;
            }
            foreach ($startTimes as $t) {
                [$h, $m] = explode(':', $t);
                $slots[] = $day->copy()->setTime((int)$h, (int)$m);
            }
        }
        shuffle($slots);
        $slotIndex = 0;

        $lineItemPool = [
            ['Labor — field technician', 4,   'hr',   95.00],
            ['Labor — field technician', 8,   'hr',   95.00],
            ['Labor — senior technician', 3,  'hr',  120.00],
            ['CAT6 cable (1000ft box)',   1,   'ea',   89.00],
            ['CAT6A cable (1000ft box)',  1,   'ea',  145.00],
            ['Keystone jacks (25-pack)',  2,   'ea',   42.00],
            ['Patch panel 24-port',       1,   'ea',  110.00],
            ['Surface mount box',         6,   'ea',    4.50],
            ['Cable management tray',     2,   'ea',   35.00],
            ['WAP installation',          1,   'ea',  150.00],
            ['Fiber splice per strand',   4,   'ea',   25.00],
            ['POE switch 8-port',         1,   'ea',  220.00],
            ['Network certification test', 24, 'drop', 15.00],
            ['Materials & supplies',      1,   'lot',  85.00],
            ['Travel charge',             1,   'ea',   45.00],
        ];

        for ($i = 0; $i < 50; $i++) {
            $customer   = $customers->random();
            $status     = $statuses[array_rand($statuses)];
            $urgency    = $urgencies[array_rand($urgencies)];
            $descIdx    = $i % count($descriptions);
            $equipIdx   = $i % count($equipmentDetails);
            $streetIdx  = $i % count($streets);
            $contactIdx = $i % count($contactNames);

            $needsSchedule = in_array($status, [
                WorkOrder::STATUS_SCHEDULED,
                WorkOrder::STATUS_SERVICES_PERFORMED,
                WorkOrder::STATUS_INVOICE_PREPARED,
                WorkOrder::STATUS_BILLED,
                WorkOrder::STATUS_COMPLETED,
            ]);

            $wo = WorkOrder::create([
                'customer_id'       => $customer->id,
                'status'            => $status,
                'urgency'           => $urgency,
                'description'       => $descriptions[$descIdx],
                'equipment_details' => $equipmentDetails[$equipIdx],
                'site_street'       => $streets[$streetIdx],
                'site_contact_name' => $contactNames[$contactIdx],
                'site_contact_phone'=> $contactPhones[$contactIdx],
                'created_by'        => $admin?->id,
            ]);

            // Attach 1–3 random services
            $wo->serviceTypes()->sync(
                $services->random(min(rand(1, 3), $services->count()))->pluck('id')
            );

            WorkOrderHistory::create([
                'work_order_id' => $wo->id,
                'changed_by'    => $admin?->id,
                'field_name'    => 'status',
                'old_value'     => null,
                'new_value'     => WorkOrder::STATUS_NEW,
                'comment'       => 'Work order created.',
                'changed_at'    => now()->subDays(rand(2, 30)),
            ]);

            // Create a visit for schedulable statuses
            if ($needsSchedule && isset($slots[$slotIndex])) {
                $visitAt = $slots[$slotIndex++];
                $duration = [60, 90, 120, 180, 240][rand(0, 4)];

                $confirmStatus = match(true) {
                    in_array($status, [WorkOrder::STATUS_SERVICES_PERFORMED, WorkOrder::STATUS_INVOICE_PREPARED, WorkOrder::STATUS_BILLED, WorkOrder::STATUS_COMPLETED])
                        => WorkOrderVisit::CONFIRMATION_CONFIRMED,
                    default => [WorkOrderVisit::CONFIRMATION_PENDING, WorkOrderVisit::CONFIRMATION_CONFIRMED][rand(0, 1)],
                };

                $visit = WorkOrderVisit::create([
                    'work_order_id'            => $wo->id,
                    'scheduled_at'             => $visitAt,
                    'duration_estimate_minutes' => $duration,
                    'confirmation_status'       => $confirmStatus,
                    'confirmed_by'             => $confirmStatus === WorkOrderVisit::CONFIRMATION_CONFIRMED ? $admin?->id : null,
                    'confirmed_at'             => $confirmStatus === WorkOrderVisit::CONFIRMATION_CONFIRMED ? now() : null,
                    'created_by'               => $admin?->id,
                ]);

                // Assign 1–2 random techs to the visit
                $visitTechs = $employees->random(min(rand(1, 2), $employees->count()));
                foreach ($visitTechs as $tech) {
                    WorkOrderVisitTech::create([
                        'visit_id'    => $visit->id,
                        'user_id'     => $tech->id,
                        'assigned_by' => $admin?->id,
                    ]);
                }
            }

            // Create invoice for half the work orders, biased toward later statuses
            $wantsInvoice = ($i % 2 === 0) || in_array($status, [
                WorkOrder::STATUS_INVOICE_PREPARED,
                WorkOrder::STATUS_BILLED,
                WorkOrder::STATUS_COMPLETED,
            ]);

            if ($wantsInvoice) {
                $invStatus = match($status) {
                    WorkOrder::STATUS_INVOICE_PREPARED => Invoice::STATUS_DRAFT,
                    WorkOrder::STATUS_BILLED           => Invoice::STATUS_ISSUED,
                    WorkOrder::STATUS_COMPLETED        => [Invoice::STATUS_PAYMENT_RECEIVED, Invoice::STATUS_COMPLETED][rand(0, 1)],
                    default                            => [Invoice::STATUS_DRAFT, Invoice::STATUS_ISSUED][rand(0, 1)],
                };

                // Pick 2–4 random line items
                $itemPool = $lineItemPool;
                shuffle($itemPool);
                $items = array_slice($itemPool, 0, rand(2, 4));

                $subtotal = array_sum(array_map(fn($it) => $it[1] * $it[3], $items));
                $taxAmt   = round($subtotal * $taxRate, 2);
                $total    = round($subtotal + $taxAmt, 2);

                $invoice = Invoice::create([
                    'work_order_id' => $wo->id,
                    'created_by'    => $admin?->id,
                    'status'        => $invStatus,
                    'subtotal'      => $subtotal,
                    'tax_rate'      => $taxRate,
                    'tax_amount'    => $taxAmt,
                    'total'         => $total,
                    'payment_terms' => $paymentTerms,
                    'footer_note'   => $footerNote,
                    'due_date'      => now()->addDays($dueDays),
                ]);

                foreach ($items as $sort => $item) {
                    InvoiceLineItem::create([
                        'invoice_id'  => $invoice->id,
                        'description' => $item[0],
                        'quantity'    => $item[1],
                        'unit'        => $item[2],
                        'unit_price'  => $item[3],
                        'sort_order'  => $sort,
                    ]);
                }
            }
        }

        $this->command->info('Created 50 demo work orders with visits and invoices.');
    }
}
