<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name'          => 'DataTel',
            'company_phone'         => '',
            'company_email'         => '',
            'company_address'       => '',
            'invoice_payment_terms' => 'Net 30',
            'invoice_footer'        => 'Thank you for your business.',
            'default_tax_rate'      => '0.0750',
            'password_min_length'   => '8',
            'sla_routine_hours'     => '48',
            'sla_urgent_hours'      => '24',
            'sla_emergency_hours'   => '4',
        ];

        foreach ($settings as $key => $value) {
            DB::table('admin_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        DB::table('tax_rates')->insertOrIgnore([
            'label'      => 'Default',
            'rate'       => 0.0750,
            'user_id'    => null,
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
