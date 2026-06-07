<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('signer_name');
            $table->string('signature_path');
            $table->foreignId('collected_by')->constrained('users');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('signed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_signatures');
    }
};
