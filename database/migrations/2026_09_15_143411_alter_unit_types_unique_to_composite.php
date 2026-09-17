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
        Schema::table('unit_types', function (Blueprint $table) {
            // Drop the single-column unique index
            $table->dropUnique(['unit_type']);
            // Add composite unique index for multi-tenancy
            $table->unique(['company_id', 'unit_type'], 'unit_types_company_unit_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_types', function (Blueprint $table) {
            $table->dropUnique('unit_types_company_unit_unique');
            $table->unique('unit_type');
        });
    }
};
