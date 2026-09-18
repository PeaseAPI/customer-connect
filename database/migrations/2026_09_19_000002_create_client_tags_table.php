<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_tags')) {
            Schema::create('client_tags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('client_id')->index();
                $table->unsignedBigInteger('tag_id')->index();
                $table->timestamps();

                $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
                $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
                $table->unique(['client_id', 'tag_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('client_tags');
    }
};