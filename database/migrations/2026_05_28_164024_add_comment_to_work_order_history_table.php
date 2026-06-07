<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_order_history', function (Blueprint $table) {
            $table->text('comment')->nullable()->after('new_value');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_history', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
};
