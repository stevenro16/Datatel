<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->unsignedSmallInteger('travel_time_minutes')->nullable()->after('duration_estimate_minutes');
            $table->string('travel_time_from')->nullable()->after('travel_time_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['travel_time_minutes', 'travel_time_from']);
        });
    }
};
