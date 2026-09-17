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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedInteger("client_id");
            $table->unsignedInteger("contract_type_id")->nullable();
            $table->string("subject");
            $table->decimal("value",16,2)->nullable();
            $table->date("start_date");
            $table->date("end_date")->nullable();
            $table->text("description")->nullable();
            $table->string("status",20)->default("draft");
            $table->string("hash");
            $table->string("signature")->nullable();
            $table->date("signed_on")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
