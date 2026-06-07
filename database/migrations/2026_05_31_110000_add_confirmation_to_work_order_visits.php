<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_visits', function (Blueprint $table) {
            $table->string('confirmation_status')->nullable()->after('notes'); // pending|confirmed|declined
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete()->after('confirmation_status');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
        });

        // Seed confirmation_status from the work order's existing confirmation_status
        // for visits that were seeded from work_orders.scheduled_at
        DB::statement("
            UPDATE work_order_visits AS v
            SET confirmation_status = (
                SELECT confirmation_status FROM work_orders WHERE work_orders.id = v.work_order_id
            )
            WHERE confirmation_status IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('work_order_visits', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['confirmation_status', 'confirmed_by', 'confirmed_at']);
        });
    }
};
