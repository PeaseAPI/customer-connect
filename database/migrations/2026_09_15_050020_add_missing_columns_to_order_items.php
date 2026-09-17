<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('order_items', 'item_summary')) {
                $table->text('item_summary')->nullable()->after('item_name');
            }
            if (!Schema::hasColumn('order_items', 'type')) {
                $table->enum('type', ['item', 'discount'])->default('item')->after('id');
            }
            if (!Schema::hasColumn('order_items', 'sku')) {
                $table->string('sku')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('order_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('order_items', 'taxes')) {
                $table->string('taxes')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('order_items', 'hsn_sac_code')) {
                $table->string('hsn_sac_code')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('order_items', 'field_order')) {
                $table->integer('field_order')->default(0)->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $cols = ['item_name', 'item_summary', 'type', 'sku', 'unit_id', 'taxes', 'hsn_sac_code', 'field_order'];
            foreach ($cols as $c) { if (Schema::hasColumn('order_items', $c)) $table->dropColumn($c); }
        });
    }
};
