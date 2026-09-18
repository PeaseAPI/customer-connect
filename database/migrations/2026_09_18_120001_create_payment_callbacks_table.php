<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_callbacks')) {
            Schema::create('payment_callbacks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('payment_id')->nullable();
                $table->string('gateway', 30)->index();
                $table->string('gateway_transaction_id')->nullable();
                $table->json('payload')->nullable();
                $table->string('status', 20)->default('pending');
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->foreign('payment_id')->references('id')->on('payments')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_callbacks');
    }
};