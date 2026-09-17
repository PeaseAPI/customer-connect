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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("subject");
            $table->text("description")->nullable();
            $table->string("status",20)->default("open");
            $table->string("priority",10)->default("medium");
            $table->unsignedInteger("agent_id")->nullable();
            $table->unsignedInteger("client_id")->nullable();
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->index("status");
            $table->index("agent_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
