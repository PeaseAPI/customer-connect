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
        Schema::create('organisation_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id")->unique();
            $table->string("company_name");
            $table->string("company_email");
            $table->string("company_phone",30)->nullable();
            $table->string("logo")->nullable();
            $table->unsignedInteger("currency_id")->nullable();
            $table->string("timezone",50)->default("Asia/Shanghai");
            $table->string("date_format",20)->default("Y-m-d");
            $table->string("time_format",10)->default("24");
            $table->string("fiscal_year",20)->nullable();
            $table->string("leaves_start_from",20)->default("joining_date");
            $table->string("active_theme",50)->default("default");
            $table->boolean("task_self")->default(false);
            $table->string("lead_source",20)->default("manual");
            $table->string("after_login",20)->default("dashboard");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_settings');
    }
};
