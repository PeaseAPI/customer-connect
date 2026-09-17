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
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('deal_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('proposal_number')->unique();
            $table->string('subject')->nullable();
            $table->string('hash')->unique();
            $table->date('valid_till')->nullable();
            $table->decimal('sub_total', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->string('discount_type', 20)->default('percent');
            $table->string('calculate_tax', 20)->default('after_discount');
            $table->string('status', 30)->default('draft');
            $table->boolean('invoice_convert')->default(false);
            $table->boolean('send_status')->default(false);
            $table->text('note')->nullable();
            $table->longText('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('signature_approval')->default(false);
            $table->text('client_comment')->nullable();
            $table->dateTime('last_viewed')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
