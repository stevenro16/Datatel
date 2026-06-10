<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes on the columns the portals filter and sort by most often. Foreign-key
 * columns are already indexed (via constrained()); these cover the status/date
 * predicates that drive the dashboards and queue listings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->index('status', 'work_orders_status_index');
            $table->index('scheduled_at', 'work_orders_scheduled_at_index');
            $table->index(['customer_id', 'status'], 'work_orders_customer_status_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('status', 'invoices_status_index');
        });

        Schema::table('work_order_notes', function (Blueprint $table) {
            $table->index('read_at', 'work_order_notes_read_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex('work_orders_status_index');
            $table->dropIndex('work_orders_scheduled_at_index');
            $table->dropIndex('work_orders_customer_status_index');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_status_index');
        });

        Schema::table('work_order_notes', function (Blueprint $table) {
            $table->dropIndex('work_order_notes_read_at_index');
        });
    }
};
