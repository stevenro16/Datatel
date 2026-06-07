<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->timestamp('scheduled_at');
            $table->smallInteger('duration_estimate_minutes')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Seed from existing work_orders.scheduled_at data (CURRENT_TIMESTAMP works in both SQLite and MySQL)
        DB::statement("
            INSERT INTO work_order_visits (work_order_id, scheduled_at, duration_estimate_minutes, created_at, updated_at)
            SELECT id, scheduled_at, duration_estimate_minutes, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM work_orders
            WHERE scheduled_at IS NOT NULL AND deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_visits');
    }
};
