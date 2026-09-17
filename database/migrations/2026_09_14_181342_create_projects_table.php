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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("project_name");
            $table->text("project_summary")->nullable();
            $table->unsignedInteger("client_id");
            $table->date("start_date");
            $table->date("deadline")->nullable();
            $table->string("status",20)->default("not_started");
            $table->string("priority",10)->default("medium");
            $table->decimal("budget",16,2)->nullable();
            $table->string("billing_type",20)->default("fixed");
            $table->decimal("hourly_rate",16,2)->nullable();
            $table->unsignedInteger("category_id")->nullable();
            $table->unsignedInteger("completion_percent")->default(0);
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->index("company_id");
            $table->index("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
