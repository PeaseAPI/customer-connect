<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('deal_id');
            $table->unsignedBigInteger('pipeline_stage_id')->nullable();
            $table->unsignedBigInteger('from_stage_id')->nullable();
            $table->string('type', 30)->default('stage_change'); // stage_change, created, note, etc
            $table->text('detail')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('deal_id')->references('id')->on('deals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_histories');
    }
};
