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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedInteger("leave_type_id");
            $table->date("leave_date");
            $table->string("duration",15)->default("full");
            $table->text("reason")->nullable();
            $table->string("status",20)->default("pending");
            $table->unsignedInteger("approved_by")->nullable();
            $table->softDeletes();
            $table->index(["user_id","leave_date"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
