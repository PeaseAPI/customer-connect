<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'discount_type')) {
                $table->enum('discount_type', ['percent', 'fixed'])->default('percent')->after('discount');
            }
            if (!Schema::hasColumn('orders', 'due_amount')) {
                $table->decimal('due_amount', 16, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('orders', 'show_shipping_address')) {
                $table->enum('show_shipping_address', ['yes', 'no'])->default('no')->after('currency_id');
            }
            if (!Schema::hasColumn('orders', 'company_address_id')) {
                $table->unsignedBigInteger('company_address_id')->nullable()->after('show_shipping_address');
            }
            if (!Schema::hasColumn('orders', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->after('client_id');
            }
            if (!Schema::hasColumn('orders', 'original_order_number')) {
                $table->string('original_order_number')->nullable()->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'last_updated_by')) {
                $table->unsignedBigInteger('last_updated_by')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['discount_type', 'due_amount', 'show_shipping_address', 'company_address_id', 'project_id', 'original_order_number', 'last_updated_by'];
            foreach ($cols as $c) { if (Schema::hasColumn('orders', $c)) $table->dropColumn($c); }
        });
    }
};
