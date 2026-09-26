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
        // Categories (e.g., Produce, Vitamins, Dairy)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Products
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique(); // Essential for inventory sync later
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('unit_price', 8, 2)->nullable(); // e.g., price per lb or oz
            $table->string('unit_type')->nullable(); // 'lb', 'oz', 'each'
            $table->string('image_url')->nullable();

            // Core attributes stored as JSON for flexibility
            // Keys: 'organic', 'non_gmo', 'gluten_free', 'vegan', 'kosher'
            $table->json('certifications')->nullable();

            // Searchable text for quick filtering
            $table->string('short_description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index(['is_active', 'category_id']);
            $table->fullText(['name', 'description', 'short_description']); // Requires MySQL/MariaDB fulltext support
        });

        // Optional: If you need to track specific attribute values (e.g., "Sugar Content: 5g")
        // For MVP, we stick to the JSON 'certifications' flag in products table for simplicity.
        // If you need complex filtering later, migrate to a pivot table.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
