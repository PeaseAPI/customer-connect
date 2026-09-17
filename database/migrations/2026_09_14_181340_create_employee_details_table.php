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
        Schema::create('employee_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("company_id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedInteger("department_id");
            $table->unsignedInteger("designation_id");
            $table->unsignedInteger("reporting_to")->nullable();
            $table->string("employee_id",50)->nullable();
            $table->date("joining_date")->nullable();
            $table->date("last_date")->nullable();
            $table->decimal("salary",16,2)->nullable();
            $table->decimal("hourly_rate",16,2)->nullable();
            $table->text("address")->nullable();
            $table->index("user_id");
            $table->index("department_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_details');
    }
};
