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
        Schema::create('approval_flows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("name");
            $table->string("type",30);
            $table->text("description")->nullable();
            $table->json("config");
            $table->string("external_type",20)->nullable();
            $table->string("external_template_id",100)->nullable();
            $table->string("status",20)->default("active");
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->index("type");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flows');
    }
};
