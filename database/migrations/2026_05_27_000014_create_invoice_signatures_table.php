<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('typed_name');
            $table->string('signature_path');
            $table->string('ip_address', 45);
            $table->timestamp('signed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_signatures');
    }
};
