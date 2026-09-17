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
                Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('bank_name');
            $table->string('branch_name')->nullable();
            $table->string('account_name');
            $table->string('account_number');
            $table->string('account_type', 30)->nullable();
            $table->string('sort_code', 30)->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->decimal('opening_balance', 16, 2)->default(0);
            $table->decimal('bank_balance', 16, 2)->default(0);
            $table->string('bank_logo')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
