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
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'pipeline_stage_id')) {
                $table->unsignedBigInteger('pipeline_stage_id')->nullable()->after('status_id');
            }
            if (!Schema::hasColumn('leads', 'client_converted_id')) {
                $table->unsignedInteger('client_converted_id')->nullable()->after('is_client');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'pipeline_stage_id')) {
                $table->dropColumn('pipeline_stage_id');
            }
            if (Schema::hasColumn('leads', 'client_converted_id')) {
                $table->dropColumn('client_converted_id');
            }
        });
    }
};
