<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add pipeline_stage_id to leads if not exists
        if (!Schema::hasColumn('leads', 'pipeline_stage_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->unsignedBigInteger('pipeline_stage_id')->nullable()->after('source_id');
                $table->foreign('pipeline_stage_id')->references('id')->on('pipeline_stages')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leads', 'pipeline_stage_id')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['pipeline_stage_id']);
                $table->dropColumn('pipeline_stage_id');
            });
        }
    }
};
