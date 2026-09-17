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
        Schema::create('client_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("company_id");
            $table->string("company_name")->nullable();
            $table->unsignedInteger("category_id")->nullable();
            $table->unsignedInteger("sub_category_id")->nullable();
            $table->text("address")->nullable();
            $table->text("shipping_address")->nullable();
            $table->text("note")->nullable();
            $table->index("user_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_details');
    }
};
