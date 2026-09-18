<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // First, convert existing string values to boolean-compatible integers
        if (Schema::hasColumn('expenses', 'billable')) {
            DB::statement("UPDATE expenses SET billable = '0' WHERE billable IS NULL OR billable = 'no'");
            DB::statement("UPDATE expenses SET billable = '1' WHERE billable = 'yes'");

            // Now drop and re-add as boolean
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('billable');
            });
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->boolean('billable')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('billable');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('billable', 3)->default('no')->after('status');
        });
    }
};
