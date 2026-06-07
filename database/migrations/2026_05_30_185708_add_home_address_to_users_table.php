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
        Schema::table('users', function (Blueprint $table) {
            $table->string('home_street', 255)->nullable()->after('phone');
            $table->string('home_city',   100)->nullable()->after('home_street');
            $table->string('home_state',   50)->nullable()->after('home_city');
            $table->string('home_zip',     20)->nullable()->after('home_state');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['home_street', 'home_city', 'home_state', 'home_zip']);
        });
    }
};
