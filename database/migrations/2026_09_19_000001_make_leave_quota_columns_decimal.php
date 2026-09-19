<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen the quota counters to allow fractional (half-day) deductions.
     */
    public function up(): void
    {
        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->decimal('leaves_used', 6, 1)->default(0)->change();
            $table->decimal('leaves_remaining', 6, 1)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->integer('leaves_used')->default(0)->change();
            $table->integer('leaves_remaining')->default(0)->change();
        });
    }
};