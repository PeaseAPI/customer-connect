<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
        public function down(): void
    {
        // Set null client_ids to 0 before making column not nullable
        DB::table('projects')->whereNull('client_id')->update(['client_id' => 0]);
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable(false)->default(0)->change();
        });
    }
};
