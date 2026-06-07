<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_visit_techs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('work_order_visits')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['visit_id', 'user_id']);
        });

        // Seed: for each existing visit, copy all WO-level assignments as visit techs
        DB::statement("
            INSERT INTO work_order_visit_techs (visit_id, user_id, assigned_by, created_at, updated_at)
            SELECT wov.id, woa.user_id, woa.assigned_by, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM work_order_visits wov
            JOIN work_order_assignments woa ON woa.work_order_id = wov.work_order_id
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_visit_techs');
    }
};
