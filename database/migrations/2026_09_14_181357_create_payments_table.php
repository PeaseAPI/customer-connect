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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("invoice_id")->nullable();
            $table->unsignedInteger("client_id");
            $table->decimal("amount",16,2);
            $table->string("gateway",20);
            $table->string("transaction_id")->nullable();
            $table->unsignedInteger("currency_id")->nullable();
            $table->string("status",20)->default("pending");
            $table->date("paid_on");
            $table->text("note")->nullable();
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->index("invoice_id");
            $table->index("gateway");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
