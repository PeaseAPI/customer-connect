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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedInteger("client_id");
            $table->string("order_number");
            $table->string("status",20)->default("pending");
            $table->decimal("sub_total",16,2)->default(0);
            $table->decimal("discount",16,2)->default(0);
            $table->decimal("total",16,2)->default(0);
            $table->decimal("tax",16,2)->default(0);
            $table->unsignedInteger("currency_id")->nullable();
            $table->date("date");
            $table->text("note")->nullable();
            $table->unsignedInteger("created_by")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
