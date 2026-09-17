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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->string("event_name");
            $table->text("description")->nullable();
            $table->timestamp("start_date_time");
            $table->timestamp("end_date_time");
            $table->string("location")->nullable();
            $table->string("repeat",20)->default("no");
            $table->unsignedInteger("repeat_every")->nullable();
            $table->string("repeat_type",20)->nullable();
            $table->date("repeat_until")->nullable();
            $table->unsignedInteger("created_by")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
