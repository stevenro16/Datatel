<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->string('image', 255)->nullable()->after('description');
        });

        // Assign placeholder images to existing services by name
        $map = [
            'Fiber Optic'            => 'fiber-optic.svg',
            'Network Infrastructure' => 'network-infrastructure.svg',
            'Structured Cabling'     => 'structured-cabling.svg',
            'Security Systems'       => 'security-systems.svg',
            'Wireless'               => 'wireless.svg',
            'Telephone / VoIP'       => 'telephone-voip.svg',
            'Testing & Certification'=> 'testing-certification.svg',
            'Consulting'             => 'consulting.svg',
        ];

        foreach ($map as $name => $file) {
            DB::table('service_types')->where('name', $name)->update(['image' => $file]);
        }
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
