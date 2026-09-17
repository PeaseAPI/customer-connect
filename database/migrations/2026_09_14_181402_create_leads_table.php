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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedInteger("agent_id")->nullable();
            $table->string("lead_name");
            $table->string("lead_email")->nullable();
            $table->string("lead_mobile",20)->nullable();
            $table->text("lead_address")->nullable();
            $table->unsignedInteger("source_id")->nullable();
            $table->unsignedInteger("status_id")->nullable();
            $table->date("next_follow_up")->nullable();
            $table->decimal("value",16,2)->nullable();
            $table->boolean("is_client")->default(false);
            $table->unsignedInteger("client_id")->nullable();
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->index("agent_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
