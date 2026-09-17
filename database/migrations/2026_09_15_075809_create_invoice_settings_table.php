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
                Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->unique();
            $table->string('invoice_prefix')->default('INV');
            $table->integer('invoice_digits')->default(3);
            $table->string('estimate_prefix')->default('EST');
            $table->string('credit_note_prefix')->default('CN');
            $table->string('invoice_number_separator')->default('-');
            $table->string('next_invoice_number')->default('1');
            $table->string('next_estimate_number')->default('1');
            $table->string('next_credit_note_number')->default('1');
            $table->string('template')->default('default');
            $table->string('due_after')->default('30'); // days
            $table->string('currency_format')->nullable();
            $table->string('decimal_separator')->default('.');
            $table->string('thousand_separator')->default(',');
            $table->boolean('show_client_note')->default(true);
            $table->boolean('show_item_tax')->default(false);
            $table->text('invoice_note')->nullable();
            $table->text('estimate_note')->nullable();
            $table->text('credit_note_note')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};
