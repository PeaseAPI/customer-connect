<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('name');
                $table->string('color', 20)->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
                $table->unique(['company_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};