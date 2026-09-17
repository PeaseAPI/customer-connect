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
        Schema::create('timelogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedInteger("task_id")->nullable();
            $table->unsignedInteger("project_id")->nullable();
            $table->unsignedBigInteger("user_id");
            $table->timestamp("start_time");
            $table->timestamp("end_time")->nullable();
            $table->decimal("total_hours",6,2)->nullable();
            $table->unsignedInteger("total_minutes")->nullable();
            $table->text("memo")->nullable();
            $table->boolean("billable")->default(true);
            $table->unsignedInteger("edited_by")->nullable();
            $table->index("user_id");
            $table->index("project_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timelogs');
    }
};
