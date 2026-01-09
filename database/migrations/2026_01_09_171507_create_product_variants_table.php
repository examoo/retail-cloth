<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // SKU & Barcode
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            
            // Variant attributes
            $table->foreignId('size_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('color_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('fabric_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('fit_id')->nullable()->constrained()->onDelete('set null');
            
            // Pricing
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->decimal('retail_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->foreignId('tax_class_id')->nullable()->constrained()->onDelete('set null');
            
            // Physical
            $table->decimal('weight', 8, 2)->nullable();
            
            // Availability
            $table->boolean('is_online')->default(true);
            $table->boolean('is_pos')->default(true);
            $table->boolean('is_active')->default(true);
            
            // Stock
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
