<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('cancel_reason')->nullable()->after('pdf_path');
        });

        // Rename status values to match new lifecycle
        DB::table('invoices')->where('status', 'new')->update(['status' => 'issued']);
        DB::table('invoices')->where('status', 'billed')->update(['status' => 'payment_received']);

        Schema::create('invoice_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users');
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('comment')->nullable();
            $table->datetime('changed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_history');

        DB::table('invoices')->where('status', 'issued')->update(['status' => 'new']);
        DB::table('invoices')->where('status', 'payment_received')->update(['status' => 'billed']);

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('cancel_reason');
        });
    }
};
