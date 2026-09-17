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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("item_name");
            $table->string("purchase_from")->nullable();
            $table->date("purchase_date");
            $table->decimal("amount",16,2);
            $table->unsignedInteger("currency_id")->nullable();
            $table->unsignedInteger("category_id")->nullable();
            $table->unsignedInteger("project_id")->nullable();
            $table->unsignedInteger("user_id")->nullable();
            $table->string("status",20)->default("pending");
            $table->string("billable",3)->default("no");
            $table->text("note")->nullable();
            $table->decimal("exchange_rate",10,4)->default(1);
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->index("project_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
