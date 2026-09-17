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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description")->nullable();
            $table->boolean("is_free")->default(false);
            $table->unsignedInteger("max_employees")->default(10);
            $table->unsignedInteger("max_storage_mb")->default(500);
            $table->decimal("monthly_price",16,2)->nullable();
            $table->decimal("annual_price",16,2)->nullable();
            $table->unsignedInteger("trial_days")->default(0);
            $table->boolean("is_default")->default(false);
            $table->boolean("is_recommended")->default(false);
            $table->unsignedInteger("sort_order")->default(0);
            $table->string("status",20)->default("active");
            $table->unsignedInteger("ai_model_tokens")->default(0);
            $table->json("modules")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
