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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('company_email');
            $table->string('company_phone', 30)->nullable();
            $table->string('logo')->nullable();
            $table->string('subdomain')->nullable()->unique();
            $table->string('custom_domain')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('license_type', 20)->default('regular');
            $table->date('license_expire_on')->nullable();
            $table->string('app_id', 50)->nullable();
            $table->string('app_secret', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
            $table->index('package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
