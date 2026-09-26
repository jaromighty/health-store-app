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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // Human-friendly: NG-2026-000123
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('fulfillment_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot fields — orders must NOT reference live mutable data
            $table->decimal('subtotal', 8, 2);
            $table->decimal('fulfillment_fee', 8, 2)->default(0);
            $table->decimal('tip', 8, 2)->default(0);
            $table->decimal('tax', 8, 2)->default(0);
            $table->decimal('total', 8, 2);

            $table->enum('status', [
                'pending_payment',
                'confirmed',
                'shopping',
                'ready_for_pickup',   // Pickup terminal path begins
                'out_for_delivery',   // Delivery path
                'completed',
                'cancelled',
            ])->default('pending_payment');

            // Payment prep (future Stripe integration)
            $table->string('payment_gateway')->default('fake');     // 'stripe' | 'fake'
            $table->string('payment_gateway_ref')->nullable();       // Stripe PaymentIntent ID
            $table->timestamp('paid_at')->nullable();

            $table->timestamp('shopped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained(); // kept for reorder lookups
            $table->string('name');       // snapshot
            $table->string('sku');        // snapshot
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2); // snapshot
            $table->decimal('line_total', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
