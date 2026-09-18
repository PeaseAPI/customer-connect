<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clients')) {
            Schema::create('clients', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('name');
                $table->string('industry', 100)->nullable();
                $table->string('contact_name', 100)->nullable();
                $table->string('contact_phone', 30)->nullable();
                $table->string('contact_email', 100)->nullable();
                $table->text('address')->nullable();
                $table->unsignedBigInteger('owner_id')->nullable();
                $table->unsignedBigInteger('level_id')->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};