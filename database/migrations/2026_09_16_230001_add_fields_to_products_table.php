<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('sub_category_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('unit_id')->nullable()->after('tax_id');
            $table->unsignedBigInteger('added_by')->nullable()->after('sku');
            $table->unsignedBigInteger('last_updated_by')->nullable()->after('added_by');
            $table->boolean('is_active')->default(true)->after('last_updated_by');

            $table->foreign('sub_category_id')->references('id')->on('product_sub_categories')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('unit_types')->nullOnDelete();
            $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('last_updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['sub_category_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['added_by']);
            $table->dropForeign(['last_updated_by']);
            $table->dropColumn(['sub_category_id', 'unit_id', 'added_by', 'last_updated_by', 'is_active']);
        });
    }
};
