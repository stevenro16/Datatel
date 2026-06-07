<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('requested_company_id')->nullable()->constrained('companies')->nullOnDelete()->after('status');
            $table->string('requested_company_name')->nullable()->after('requested_company_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['requested_company_id']);
            $table->dropColumn(['requested_company_id', 'requested_company_name']);
        });
    }
};
