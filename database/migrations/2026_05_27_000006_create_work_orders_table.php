<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', [
                'new', 'triaged', 'scheduled', 'awaiting_feedback',
                'services_performed', 'invoice_prepared', 'billed', 'completed', 'canceled'
            ])->default('new');
            $table->enum('urgency', ['routine', 'urgent', 'emergency'])->default('routine');
            $table->enum('building_type', ['commercial', 'residential', 'industrial', 'data_center', 'other'])->nullable();

            // Site address (either FK to saved address or manual entry)
            $table->foreignId('site_address_id')->nullable()->constrained('customer_addresses')->nullOnDelete();
            $table->string('site_street')->nullable();
            $table->string('site_city')->nullable();
            $table->string('site_state', 2)->nullable();
            $table->string('site_zip', 10)->nullable();

            // On-site contact
            $table->string('site_contact_name')->nullable();
            $table->string('site_contact_phone', 20)->nullable();

            $table->text('description');
            $table->text('tech_questions')->nullable();
            $table->integer('num_drops')->nullable();
            $table->string('circuit_ref')->nullable();
            $table->date('preferred_date')->nullable();
            $table->time('availability_from')->nullable();
            $table->time('availability_to')->nullable();

            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->integer('duration_estimate_minutes')->nullable();

            // Cancelation
            $table->text('cancel_reason')->nullable();
            $table->foreignId('canceled_by')->nullable()->constrained('users');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
