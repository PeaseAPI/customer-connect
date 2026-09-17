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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedInteger("client_id");
            $table->unsignedInteger("project_id")->nullable();
            $table->string("invoice_number");
            $table->decimal("sub_total",16,2);
            $table->decimal("discount",16,2)->default(0);
            $table->string("discount_type",10)->default("percent");
            $table->decimal("total",16,2);
            $table->decimal("tax",16,2)->default(0);
            $table->unsignedInteger("currency_id")->nullable();
            $table->string("status",20)->default("draft");
            $table->date("date");
            $table->date("due_date");
            $table->text("note")->nullable();
            $table->string("recurring",3)->default("no");
            $table->string("recurring_cycle",20)->nullable();
            $table->date("recurring_next_date")->nullable();
            $table->string("hash");
            $table->date("sent_on")->nullable();
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->index("client_id");
            $table->index("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
