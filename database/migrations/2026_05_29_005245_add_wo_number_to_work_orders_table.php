<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('wo_number')->nullable()->unique()->after('id');
        });

        // Backfill existing records sequentially starting at 10000
        $next = 10000;
        DB::table('work_orders')->orderBy('id')->each(function ($row) use (&$next) {
            DB::table('work_orders')->where('id', $row->id)->update(['wo_number' => $next++]);
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('wo_number');
        });
    }
};
