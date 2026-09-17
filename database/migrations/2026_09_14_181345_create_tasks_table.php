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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedInteger("project_id")->nullable();
            $table->unsignedInteger("milestone_id")->nullable();
            $table->string("title");
            $table->text("description")->nullable();
            $table->string("priority",10)->default("medium");
            $table->date("start_date")->nullable();
            $table->date("due_date")->nullable();
            $table->string("status",20)->default("pending");
            $table->boolean("is_pinned")->default(false);
            $table->unsignedInteger("parent_task_id")->nullable();
            $table->unsignedBigInteger("assign_to")->nullable();
            $table->decimal("estimate_hours",6,2)->nullable();
            $table->unsignedInteger("created_by")->nullable();
            $table->timestamp("completed_on")->nullable();
            $table->softDeletes();
            $table->index("company_id");
            $table->index("project_id");
            $table->index("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
