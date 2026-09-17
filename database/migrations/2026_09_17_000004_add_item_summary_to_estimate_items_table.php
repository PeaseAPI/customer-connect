<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            if (!Schema::hasColumn('estimate_items', 'item_summary')) {
                $table->text('item_summary')->nullable()->after('item_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            if (Schema::hasColumn('estimate_items', 'item_summary')) {
                $table->dropColumn('item_summary');
            }
        });
    }
};
