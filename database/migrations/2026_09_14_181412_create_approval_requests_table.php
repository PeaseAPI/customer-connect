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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("flow_id");
            $table->unsignedBigInteger("user_id");
            $table->json("form_data");
            $table->string("status",20)->default("draft");
            $table->unsignedInteger("current_step")->default(1);
            $table->string("external_instance_id",100)->nullable();
            $table->softDeletes();
            $table->index("user_id");
            $table->index("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
