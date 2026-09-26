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
        // Pickup & delivery zones (drives address validation for delivery)
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('zip_code'); // Simple zip-based geofence for MVP
            $table->enum('fulfillment_type', ['delivery', 'pickup'])->default('delivery');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'zip_code', 'fulfillment_type']);
        });

        // Slot templates (admin defines recurring windows per store)
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->enum('fulfillment_type', ['delivery', 'pickup']);
            $table->string('day_of_week'); // 'mon'...'sun'
            $table->time('starts_at');
            $table->time('ends_at');
            $table->integer('capacity')->default(10); // Max orders per slot
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Concrete slot instances (materialized from templates, tracks bookings)
        Schema::create('slot_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            $table->date('slot_date');
            $table->integer('booked_count')->default(0);
            $table->timestamps();

            $table->unique(['time_slot_id', 'slot_date']);
        });

        // The unified fulfillment record attached to a checkout/order
        Schema::create('fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('slot_booking_id')->nullable()->constrained()->cascadeOnDelete();
            $table->enum('type', ['delivery', 'pickup']);
            $table->enum('status', ['scheduled', 'ready', 'out_for_delivery', 'completed', 'cancelled'])->default('scheduled');

            // Delivery fields (null for pickup)
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_state', 2)->nullable();
            $table->string('delivery_zip', 10)->nullable();

            // Pickup fields (null for delivery)
            $table->string('vehicle_description')->nullable(); // e.g., "Blue Honda CR-V"

            $table->decimal('fee', 8, 2)->default(0);
            $table->decimal('tip', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fulfillments');
        Schema::dropIfExists('slot_bookings');
        Schema::dropIfExists('time_slots');
        Schema::dropIfExists('service_areas');
    }
};
