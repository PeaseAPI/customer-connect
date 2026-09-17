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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->date("date");
            $table->timestamp("clock_in_time")->nullable();
            $table->timestamp("clock_out_time")->nullable();
            $table->string("clock_in_ip",45)->nullable();
            $table->string("clock_out_ip",45)->nullable();
            $table->string("late",3)->default("no");
            $table->unsignedInteger("late_by")->nullable();
            $table->string("half_day",3)->default("no");
            $table->unsignedInteger("shift_id")->nullable();
            $table->string("working_from",10)->nullable();
            $table->unique(["user_id","date"]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
