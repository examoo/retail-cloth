<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add gender and age_group to categories
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('gender', ['men', 'women', 'unisex'])->nullable()->after('parent_id');
            $table->enum('age_group', ['baby', 'kids', 'adult'])->nullable()->after('gender');
            $table->string('image_url')->nullable()->after('age_group');
            $table->integer('sort_order')->default(0)->after('is_active');
            $table->string('meta_title', 70)->nullable()->after('sort_order');
            $table->string('meta_description', 170)->nullable()->after('meta_title');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['gender', 'age_group', 'image_url', 'sort_order', 'meta_title', 'meta_description']);
        });
    }
};
