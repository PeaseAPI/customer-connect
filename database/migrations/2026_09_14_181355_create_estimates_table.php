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
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedInteger("client_id");
            $table->string("estimate_number");
            $table->decimal("sub_total",16,2);
            $table->decimal("discount",16,2)->default(0);
            $table->decimal("total",16,2);
            $table->decimal("tax",16,2)->default(0);
            $table->unsignedInteger("currency_id")->nullable();
            $table->string("status",20)->default("pending");
            $table->date("date");
            $table->date("valid_till");
            $table->text("note")->nullable();
            $table->string("hash");
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
