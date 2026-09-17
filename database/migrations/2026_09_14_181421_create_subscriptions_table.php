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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("package_id");
            $table->string("stripe_id")->nullable();
            $table->string("status",20)->default("active");
            $table->timestamp("trial_ends_at")->nullable();
            $table->timestamp("ends_at")->nullable();
            $table->unsignedInteger("quantity")->default(1);
            $table->index("company_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
