<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Product type & classification
            $table->enum('product_type', ['stitched', 'unstitched'])->default('stitched')->after('description');
            $table->string('short_description', 500)->nullable()->after('description');
            
            // Additional details
            $table->string('fabric_type')->nullable()->after('brand_id');
            $table->text('care_instructions')->nullable()->after('fabric_type');
            $table->string('season')->nullable()->after('care_instructions');
            
            // Status & flags
            $table->enum('status', ['draft', 'published', 'pos_only', 'online_only'])->default('draft')->after('is_active');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('is_bestseller')->default(false)->after('is_featured');
            
            // SEO fields
            $table->string('meta_title', 70)->nullable()->after('is_bestseller');
            $table->string('meta_description', 170)->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
            $table->string('og_image')->nullable()->after('meta_keywords');
            $table->text('json_ld_schema')->nullable()->after('og_image');
            $table->string('canonical_url')->nullable()->after('json_ld_schema');
            
            // Soft deletes
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_type', 'short_description', 'fabric_type', 'care_instructions', 'season',
                'status', 'is_featured', 'is_bestseller',
                'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'json_ld_schema', 'canonical_url',
                'deleted_at'
            ]);
        });
    }
};
