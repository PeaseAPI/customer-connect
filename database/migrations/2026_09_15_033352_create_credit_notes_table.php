<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('cn_number')->unique();
            $table->date('issue_date');
            $table->decimal('discount', 16, 2)->default(0);
            $table->string('discount_type', 20)->default('percent');
            $table->decimal('sub_total', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->string('status', 30)->default('open'); // open, closed, draft
            $table->text('note')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
