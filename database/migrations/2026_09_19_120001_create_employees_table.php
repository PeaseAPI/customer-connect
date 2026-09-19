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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('employee_code', 50)->nullable();
            $table->string('name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->date('joined_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};