<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('proposal_id');
            $table->string('item_name');
            $table->text('item_summary')->nullable();
            $table->string('hsn_sac_code', 30)->nullable();
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 16, 2)->default(0);
            $table->decimal('amount', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->string('discount_type', 20)->default('percent');
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('proposal_id')->references('id')->on('proposals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_items');
    }
};
